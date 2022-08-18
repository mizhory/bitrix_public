<?php

namespace Immo\Statements\Tools;

use Immo\Statements\Data\HLBlock;
use Immo\Statements\Generation\Helper;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Repository\EmployeeRepository;
use Immo\Statements\Repository\StatementIblock;
use Immo\Statements\Traits\IblockTrait;
use CBPSequentialWorkflowActivity;
use Immo\Statements\Repository\StatementsApproval;
use Immo\Statements\Workflow\Workflow;

/**
 * Выполеннеие различных работ в БП
 * <code>
 *      // Смена статуса для HL записей
 *      \Bitrix\Main\Loader::includeModule('immo.statements');
 *      \Immo\Statements\Tools\ActivityTools::setNextStatus($this->GetRootActivity());
 *      \Immo\Statements\Tools\ActivityTools::checkStartSecondStep($this->GetRootActivity());
 * </code>
 */
class ActivityTools implements ModuleInterface
{

    use Helper;

    /**
     * Смена статуса для HL записей
     * @param CBPSequentialWorkflowActivity $rootActivity
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function setNextStatus(CBPSequentialWorkflowActivity $rootActivity)
    {
        $iLegalPerson = (int)$rootActivity->getVariable("iLegalPerson");
        $iBusinessUnit = (int)$rootActivity->getVariable("iBusinessUnit");
        $iDocumentId = (int)$rootActivity->getVariable("iDocumentId");

        $iIdEnumStatusCurrent = (int)$rootActivity->getVariable("iIdEnumStatusCurrent");
        $iIdEnumStatusNext = (int)$rootActivity->getVariable("iIdEnumStatusNext");

        static::setStatusZpv(
            $iDocumentId,
            $iBusinessUnit,
            $iLegalPerson,
            $iIdEnumStatusCurrent,
            $iIdEnumStatusNext
        );
    }

    /**
     * Проверка на возможность запуска второго (Согласование с финдирекцией/блока бухгалтерии) этапа БП
     * Записывает ошибки в переменную БП arErrors
     * <code>
     *      // Проверка, можно ли перейти на второй этап (Согласование с финдирекцией/блока бухгалтерии)
     *      \Bitrix\Main\Loader::includeModule('immo.statements');
     *      \Immo\Statements\Tools\ActivityTools::checkStartSecondStep($this->GetRootActivity());
     * </code>
     * @param CBPSequentialWorkflowActivity $rootActivity
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function checkStartSecondStep(CBPSequentialWorkflowActivity $rootActivity)
    {

        $iIdEnumStatusCurrent = (int)$rootActivity->getVariable("iIdEnumStatusCurrent");
        $iYear = (int)$rootActivity->getVariable("iYear");
        $sMonthName = (string)$rootActivity->getVariable("sMonthName");

        $obStatementIblock = StatementIblock::getInstance();
        $sMonthCode = $obStatementIblock->getMonthXmlIdByValue($sMonthName);
        $sStatusCodeCurrent = $obStatementIblock->getStatusCardXmlIdByEnumId($iIdEnumStatusCurrent);


        $iIblockId = IblockTrait::getIblockId(static::IBLOCK_CODE_LABELS_SALARY, static::IBLOCK_TYPE_BITRIX_PROCESSES);
        $arEnumValues = IblockTrait::getPropertiesListValues('F_MONTH', $iIblockId, null, true);
        $sMonthCode = '';

        foreach ($arEnumValues as $arEnumValue) {
            if ($arEnumValue['value'] === $sMonthName) {
                $sMonthCode = $arEnumValue['xml_id'];
                break;
            }
        }


        // количество штатных сотрудников в компании
        $iMaxEmployees = \Immo\Statements\Data\Department::getCountEmployees();
        $ob = \Immo\Statements\Repository\StatementsApproval::getInstance();

        // количество сотрудников у кого есть ЯЗПВ
        $iEmployeesInMonth = $ob->getCountUserByDate($iYear, $sMonthCode);
        $arErrors = [];
        /*
         * В компании штатных сотрудников $iMaxEmployees, а количество сотруднико имеющих ЯЗПВ $iEmployeesInMonth
         * если они не равтны, то кому-то не создали ЯЗПВ или у кого-то не должно быть ЯЗПВ
         */
        if ($iMaxEmployees != $iEmployeesInMonth) {
            $arErrors[] = 'Работников ' . $iMaxEmployees . ', а в ЯЗПВ ' . $iEmployeesInMonth;
        }
        $arEnumValues = IblockTrait::getPropertiesListValues('STATUS_CARD', $iIblockId, null, true);
        $sStatusCodeCurrent = false;
        if (array_key_exists($iIdEnumStatusCurrent, $arEnumValues)) {
            $sStatusCodeCurrent = $arEnumValues[$iIdEnumStatusCurrent]['xml_id'];
        }
        /*
         * Количество бланков в текущем статусе, если они есть, то надо дождаться всех, т.е. в текущем
         * статусе не должно остаться ЯЗПВ
         */
        $iEmployeesInMonth = $ob->getCountStatement($iYear, $sMonthCode, $sStatusCodeCurrent);
        if ($iEmployeesInMonth !== 0) {
            $arErrors[] = 'Есть ЯЗПВ ' . $iEmployeesInMonth . ', в статусе ' . $sStatusCodeCurrent;
        }
        $rootActivity->setVariable("arErrors", $arErrors);
    }

    /**
     * Запуск этап Ответственный за заполнение ЗП Ведомости
     * @description В БП должны быть переменные:
     * iBusinessUnit - ID БЕ (из структуры)
     * @param CBPSequentialWorkflowActivity $rootActivity
     * @return void
     */
    public static function runResponsibleFillingStatement(CBPSequentialWorkflowActivity $rootActivity): void
    {

        $iYear = (int)$rootActivity->getVariable("iYear");
        $sMonthName = (string)$rootActivity->getVariable("sMonthName");

        $obStatementsApproval = StatementsApproval::getInstance();
        $iMonthEnumId = $obStatementsApproval->getMonthEnumIdByValue($sMonthName);
        /*
         * пользовтели которые участвую в ЗПВ по дате
         */
        $arUserIds = $obStatementsApproval->getUserIdsStatementByDate($iYear, $iMonthEnumId);
        /*
         * Выбор внештатных сотрудников
         */
        $arFreelancesEmployees = EmployeeRepository::getFreelanceEmployee($arUserIds);
        /*
         * Создание анкет для внештатних сотрудников
         */
        foreach ($arFreelancesEmployees as $arFreelancesEmployee) {
            $arFreelancesEmployee['UF_MONTH'] = $iMonthEnumId;
            $arFreelancesEmployee['UF_YEAR'] = $iYear;
            $obStatementsApproval->generationFreelanceEmployee($arFreelancesEmployee);
        }
        /*
         * $arStatementsApprovalGroup - для каких БЕ надо создать ЗПВ с учетом типа сотрудников
         */
        $arStatementsApprovalGroup = $obStatementsApproval->getStatementsByDate(
            $iYear,
            $iMonthEnumId
        );
        /*
         * Создание ЗПВ
         */
        $obStatementIblock = StatementIblock::getInstance();
        $arStatementsApprovalBindingForBusinessUnit = $obStatementIblock->generateStatements($arStatementsApprovalGroup,
            $iYear,
            $obStatementIblock->getMonthEnumIdByValue($sMonthName)
        );
        // привязка ЯЗПВ к новым ЗПВ по БЕ
        foreach ($arStatementsApprovalBindingForBusinessUnit as $iBusinessUnitId => $iStatementsApproval) {
            // Всем ЯЗПВ устанвливается новый ярлык
            StatementsApproval::updateMulti($iStatementsApproval, ['UF_LABELS_SALARY_ELEMENT_ID' => $iBusinessUnitId] );
            $obStatementsApproval->generateSortForBusinessUnit($iBusinessUnitId);
            // Запуск БП
            Workflow::startBpStart($iBusinessUnitId);
        }
    }

    /**
     * Установка новых статусов для отображения по ЮЛ
     * @param int $iIdZpv
     * @param int $iIdBe
     * @param int $iIdUr
     * @param int $iIdEnumStatusCurrent
     * @param int $iIdEnumStatusNext
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function setStatusZpv(int $iIdZpv, int $iIdBe, int $iIdUr, int $iIdEnumStatusCurrent, int $iIdEnumStatusNext): void
    {
        /*
         * из enum_id Статус согласования[STATUS_CARD] получение xml_id свойств
         */
        $sStatusCodeCurrent = StatementIblock::getInstance()->getStatusCardXmlIdByEnumId($iIdEnumStatusCurrent);
        $sStatusCodeNext = StatementIblock::getInstance()->getStatusCardXmlIdByEnumId($iIdEnumStatusNext);

        if ($sStatusCodeCurrent === 'buhCompany' && $sStatusCodeNext === 'direcBe') {
            /*
             * Переход из статуса  -> в статус
             * Формирование у бухгалтера юр лица -> Согласование с ответственным за заполнение ЗП Ведомости
             */
            $arFilter = [
                '=UF_PRE_SALARY_ELEMENT_ID' => $iIdZpv,
                '=UF_COMPANY' => $iIdUr,
            ];
            static::setStatus($arFilter, $sStatusCodeCurrent, $sStatusCodeNext);
        } elseif ($sStatusCodeCurrent === 'direcBe' && $sStatusCodeNext === 'fd') {
            /*
             * Переход из статуса  -> в статус
             * Согласование с ответственным за заполнение ЗП Ведомости -> Согласование с Финдирекцией/блока бухгалтерии
             */
            $arFilter = [
                '=UF_LABELS_SALARY_ELEMENT_ID' => $iIdZpv,
                '=UF_BE' => $iIdBe,
            ];
            static::setStatus($arFilter, $sStatusCodeCurrent, $sStatusCodeNext);
        }

    }

    /**
     * Установка новых статусов для ЯЗПВ
     * @param array $arFilterForFind - Фильтр для поиска
     * @param string $sStatusCodeCurrent - xml_id код статуса
     * @param string $sStatusCodeNext -  xml_id код статуса
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function setStatus(array $arFilterForFind, string $sStatusCodeCurrent, string $sStatusCodeNext): void
    {

        $obHlBlockStatements = new HLBlock(static::HL_ENTITY_STATEMENTS_APPROVAL);
        $arStatusCodeCurrent = static::getHlEnum($obHlBlockStatements->getUfEntityId(), 'UF_STATUS_CARD', $sStatusCodeCurrent);
        $arStatusCodeNext = static::getHlEnum($obHlBlockStatements->getUfEntityId(), 'UF_STATUS_CARD', $sStatusCodeNext);
        $sClassStatements = $obHlBlockStatements->getEntity();
        $arFilter = [
            '=UF_STATUS_CARD' => $arStatusCodeCurrent['ID']
        ];
        $arFilter = array_merge($arFilter, $arFilterForFind);
        $obRes = $sClassStatements::getList(
            [
                'filter' => $arFilter,
                'select' => [
                    'ID'
                ]
            ]
        );
        $arIds = [];
        while ($arSalaryUser = $obRes->fetch()) {
            $arIds[] = $arSalaryUser['ID'];
        }
        if ($arIds) {
            $sClassStatements::updateMulti($arIds, ['UF_STATUS_CARD' => $arStatusCodeNext['ID']], true);
        }
    }
}