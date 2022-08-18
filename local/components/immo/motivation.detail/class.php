<?php

namespace Immo\Components;

use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use CBPDocument;
use Immo\Iblock\Manager;
use Immo\Iblock\Property\BiznesUnitsIblockField;
use Bitrix\Main;
use CIBlockElement;
use Bitrix\Highloadblock as HL;
use CUser;
use CIBlockPropertyEnum;
use CUserFieldEnum;
use Bitrix\Main\Engine\ActionFilter;
use Immo\Motivation\Access;
use Immo\Iblock\RightsManagerTrait;
use Immo\Motivation\ArticleExpenses;
use Immo\Motivation\Budget;
use Immo\Motivation\BusinessUnits;
use Immo\Tools\BizprocHelper;
use Bitrix\Highloadblock\HighloadBlockTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @description Отображение Премиальной ведомости
 */
class MotivationDetail extends \CBitrixComponent implements Main\Engine\Contract\Controllerable
{
    use RightsManagerTrait;

    private ?array $arStatusCard = null;

    /**
     * @return mixed|void|null
     * @throws Main\ArgumentException
     * @throws Main\Db\SqlQueryException
     */
    public function executeComponent()
    {
        global $USER;

        Main\UI\Extension::load("ui.buttons");
        Main\UI\Extension::load('ui.entity-selector');
        Main\UI\Extension::load("ui.dialogs.messagebox");
        Main\UI\Extension::load("ui.alerts");
        $sTemplate = null;
        /**
         * Определение бюджетного года
         */
        $this->arResult = $this->getMotivationData($this->arParams['ID']);


        $this->arResult['USER_ID'] = $USER->GetID();
        // Запрашиваемая ведомость не найдена
        $this->arResult['TEXT_ERROR'] = 'Запрашиваемая ведомость не найдена';
        $this->arParams['ID'] = (int)$this->arParams['ID'];
        $sTemplate = 'error';
        if ($this->arParams['ID'] === 0) {
            if ($this->canCreate()) {
                $sTemplate = 'show_start';
            }
        } else {
            if ($this->arParams['ID'] > 0 && $this->arResult['ID'] > 0) {
                $sTemplate = mb_strtolower('show_' . $this->arResult['STATUS_CARD_XML_ID']);
                if ($this->arResult['STATUS_CARD_XML_ID'] !== 'START') {
                    $sTemplate = mb_strtolower('show');
                }
                if ($this->arResult['STATUS_CARD_XML_ID'] === 'REJECTED' && $this->canEdit($this->arParams['ID'])) {
                    $sTemplate = 'show_start';
                }
                if ($this->request->getQuery('isRun') === 'Y') {
                    $sTemplate = 'show_success';
                }
            }
        }
        $this->arResult['FORM']['SELECTED_BE'] = $this->getBusinessUnits();
        $this->arResult['FORM']['FISCAL_YEAR'] = $this->getFiscalYear();
        $this->arResult['FORM']['MONTH'] = $this->getMonth();
        $this->arResult['FORM']['ART'] = $this->getItemExpenses();

        $this->arResult['FORM']['CURRENCY'] = "RUB";
        $this->arResult['FORM']['USER_IN_MOTIVATION'] = $this->getUsersByMotivationId(
            $this->arResult['ID'],
            $this->bIsAccountant($this->arResult['SELECTED_BE'], $this->arResult['IBLOCK_ID'])
        );
        // Сумма по ведомости
        $this->arResult['STATEMENT_AMOUNT'] = 0;
        if ($this->arResult['FORM']['USER_IN_MOTIVATION']) {
            foreach ($this->arResult['FORM']['USER_IN_MOTIVATION'] as $arUserInMotivation) {
                $this->arResult['STATEMENT_AMOUNT'] += $arUserInMotivation['SUM_FORMATTED'];
            }
        }
        $this->arResult['FORM']['ADDITIONAL_USERS'] = $this->getAdditionalUsers();
        $this->arResult['FORM']['SELECTED_ADDITIONAL_USERS'] = $this->getSelectedAdditionalUsers(
            $this->arResult['ADDITIONAL_USERS']
        );
        $this->arResult['BP_TEMPLATES_IDS'] = $this->getBpWorkflowTemplateByStatusCard($this->arResult['STATUS_CARD_XML_ID']);
        if ($this->request->isPost()) {
            if ($this->request->getPost('SEND_TO_AGREE') === 'Y') {
                $arField = $this->request->getPostList()->toArray();
                // если есть подмена данных в форме
                $arField['ID'] = (int)$this->arResult['ID'];
                // проверка полей, проверяются данные, которые
                // в значениях на соотвестве.
                $this->checkFields($arField);
                // контрольное сохрание
                $result = $this->saveAction(['data' => $arField]);
                // запуск бп
                $obStartWorkflow = $this->startWorkflow($result['ID']);
                $sCodeError = '';
                /** @var Main\Error $obError */
                foreach ($obStartWorkflow->getErrorCollection() as $obError) {
                    $sCodeError = $obError->getCode();
                }
                global $APPLICATION;
                $sUrl = $APPLICATION->GetCurDir() . '?isRun=Y';
                if ($sCodeError) {
                    $sUrl = $APPLICATION->GetCurDir() . ($sCodeError ? '?codeError=' . $sCodeError : '');
                }
                LocalRedirect($sUrl);
            } elseif ($this->request->getPost('CANCEL') === 'Y') {
                $this->deleteStatement((int)$this->arResult['ID']);
                LocalRedirect($this->arParams['LIST_URL']);
            }
        }
        // строка согласующих
        $this->initUserAgreeInLine();

        if ($this->arParams['ID'] === 0) {
            $sMonthName = FormatDate('f', time());
            foreach ($this->arResult['FORM']['MONTH'] as $arMonth) {
                if ($arMonth['VALUE'] === $sMonthName) {
                    $this->arResult['F_MONTH'] = $arMonth['ID'];
                    break;
                }
            }
        }
        if ($this->request->getQuery('codeError') === 'create_reserve') {
            $sTemplate = 'error';
            $this->arResult['TEXT_ERROR'] = 'Средств для списания недостаточно. Просьба обратиться в фин дирекцию';
        }

        $this->includeComponentTemplate($sTemplate);
    }

    /**
     * Получить БЕ для текущего пользователя
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getBusinessUnits(): array
    {
        $obBusinessUnits = new BusinessUnits();
        return $obBusinessUnits->availableBusinessUnitsForCurrenUser();
    }

    /**
     * Получить Финансовый год
     * @return int
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     */
    private function getFiscalYear(): int
    {
        return BiznesUnitsIblockField::defineFinancialYear();
    }

    /**
     * Получить список меяцев из свойства
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getMonth(): array
    {
        $iIblockId = Manager::getIblockId('motivation');
        $arProps = Manager::getPropertyByCode('F_MONTH', $iIblockId);
        if ($arProps['VALUES']) {
            // сортровка
            $arResult = [
                'MAY' => [],
                'JUNE' => [],
                'JULY' => [],
                'AUGUST' => [],
                'SEPTEMBER' => [],
                'OCTOBER' => [],
                'NOVEMBER' => [],
                'DECEMBER' => [],
                'JANUARY' => [],
                'FEBRUARY' => [],
                'MARCH' => [],
                'APRIL' => [],
            ];
            foreach ($arProps['VALUES'] as $arValue) {
                $arResult[$arValue['XML_ID']] = $arValue;
            }
            return $arResult;
        }
        return [];
    }

    /**
     * Получить статьи
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getItemExpenses(): array
    {
        $obArticles = new ArticleExpenses();
        return $obArticles->getArticles();
    }

    public function configureActions()
    {
        return [
            'save' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ])

                ],
            ]
        ];
    }

    /**
     * Соранение анкеты ajax
     * @param array $arField
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function saveAction(array $arField = []): array
    {
        global $DB;
        $id = $arField['data']['ID'];
        $sErrorMessage = '';
        if (!$this->canCreate()) {
            return [
                "ID" => $id,
                'sErrorMessage' => 'У вас нет прав на создание'
            ];
        }
        try {
            $DB->StartTransaction();
            if ($arField['data']['ID'] <= 0) {
                $id = $this->createStatement($arField['data']);
            } else {
                $this->updateStatement($id, $arField['data']);
            }
            $this->saveUserInStatement($id, $arField['data']);
            //пресчет суммы по ведомости (данные выбираются из БД)
            $this->saveStatementAmount($id);
            $DB->commit();
        } catch (Main\DB\Exception $exception) {
            $DB->Rollback();
            $sErrorMessage = $exception->getMessage();
        }

        return array_merge(
            [
                "ID" => $id,
                'sErrorMessage' => $sErrorMessage
            ],
            $this->getMotivationData($id)
        );
    }

    /**
     * Сохранение анкеты
     * @param array $arFields
     * @return int
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function createStatement(array $arFields): int
    {
        global $USER;
        $iIblockId = Manager::getIblockId('motivation');
        $PROP = [];
        $PROP['SELECTED_BE'] = $arFields['SELECTED_BE'];
        $PROP['F_YEAR'] = $arFields['F_YEAR'];
        $PROP['F_MONTH'] = $arFields['F_MONTH'];
        $PROP['SELECTED_ART'] = $arFields['SELECTED_ART'];
        $PROP['ADDITIONAL_USERS'] = $arFields['ADDITIONAL_USERS'];
        $PROP['STATUS_CARD'] = $this->getStartStatusId();
        $PROP['ASSIGNED_BY'] = $USER->GetID();
        $arFields = [
            'IBLOCK_ID' => $iIblockId,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => "Премиальная ведомость №0",
            "ACTIVE" => "Y",
            "PREVIEW_TEXT" => $arFields['PREVIEW_TEXT'],
        ];
        $obElem = new CIBlockElement();
        $iMotivationId = $obElem->Add($arFields, false, false);
        // установка прав
        $this->setMotivationRight($iMotivationId);
        $arLogFields = [
            'UF_ELEMENT_ID' => $iMotivationId,
            'UF_IBLOCK_ID' => $iIblockId,
            'UF_ACTION_TITLE' => 'Создание новой ведомости',
            'UF_DATE_CREATE' => new Main\Type\DateTime(),
            'UF_USER_ID' => $USER->GetID(),
        ];
        $sClassActivityLoggingTable = HighloadBlockTable::compileEntity('ActivityLogging')->getDataClass();
        $obResult = $sClassActivityLoggingTable::add($arLogFields);
        if (!$iMotivationId) {
            throw new Main\DB\Exception('motivation is not saved on create', $obElem->LAST_ERROR);
        }
        return (int)$iMotivationId;
    }

    /**
     * Обновление анкеты
     * @param int $id
     * @param array $arFields
     * @return int
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function updateStatement(int $id, array $arFields): int
    {
        $iIblockId = Manager::getIblockId('motivation');
        $PROP = [];
        $PROP['SELECTED_BE'] = ["VALUE" => $arFields['SELECTED_BE']];
        $PROP['F_YEAR'] = ["VALUE" => $arFields['F_YEAR']];
        $PROP['F_MONTH'] = ["VALUE" => $arFields['F_MONTH']];
        $PROP['SELECTED_ART'] = ["VALUE" => $arFields['SELECTED_ART']];
        if (!is_array($arFields['ADDITIONAL_USERS'])) {
            $arFields['ADDITIONAL_USERS'] = [$arFields['ADDITIONAL_USERS']];
        }
        $PROP['ADDITIONAL_USERS'] = [];
        foreach ($arFields['ADDITIONAL_USERS'] as $key => $iUserId) {
            $PROP['ADDITIONAL_USERS'][] = ["VALUE" => $iUserId, 'DESCRIPTION' => ''];
        }
        if ($arFields['STATUS_CARD'] > 0) {
            $PROP['STATUS_CARD'] = ["VALUE" => $arFields['STATUS_CARD']];
        }
        $arFields = [
            'IBLOCK_ID' => $iIblockId,
            "NAME" => "Премиальная ведомость №" . $id,
            "ACTIVE" => "Y",
            "PREVIEW_TEXT" => $arFields['PREVIEW_TEXT'],
        ];
        $obElem = new CIBlockElement();
        $bResult = $obElem->Update($id, $arFields, false, false);
        CIBlockElement::SetPropertyValuesEx(
            $id,
            $iIblockId,
            $PROP
        );
        if (!$bResult) {
            throw new Main\DB\Exception('motivation is not saved on update', $obElem->LAST_ERROR);
        }
        return (int)$bResult;
    }

    /**
     * @param int $iStatementId
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function deleteStatement(int $iStatementId)
    {
        global $DB;
        try {
            $DB->StartTransaction();
            $bIsDeleteStatement = CIBlockElement::Delete($iStatementId);
            if (!$bIsDeleteStatement) {
                throw new Main\DB\Exception(
                    'Statement is not delete',
                );
            }
            $arUsersInMotivation = $this->getUsersByMotivationId($iStatementId);
            foreach ($arUsersInMotivation as $arUserInExPv) {
                $obResult = $this->getClassNameHlExpv()::delete($arUserInExPv['ID']);
                if (!$obResult->isSuccess()) {
                    throw new Main\DB\Exception(
                        'user in motivation is bad on delete',
                        implode(',', $obResult->getErrorMessages())
                    );
                }
            }
            $DB->commit();
        } catch (Main\DB\Exception $exception) {
            $DB->Rollback();
            throw $exception;
        }
    }

    /**
     * Получение данных по ведомости из БД
     * @param int $iMotivationId
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getMotivationData(int $iMotivationId): array
    {
        $iIblockId = Manager::getIblockId('motivation');
        $arResult = [
            'ID' => 0,
            'SELECTED_BE' => 0,
            'SELECTED_ART' => 0,
            'F_MONTH' => 0,
            'I_MONTH' => 0,
            'F_YEAR' => 0,
            'F_MONTH_VALUE' => '',
            'PREVIEW_TEXT' => '',
            'ADDITIONAL_USERS' => [],
            'TO_USERS' => [],
            //Осталось доступных средств = Баланс НИ
            'BALANCE_ACCUMULATIVE' => 0,
            //Доступно за весь период = План
            'BALANCE_PLAN' => 0,
            //Всего истрачено за период = Факт
            'BALANCE_FACT' => 0,

            'IBLOCK_ID' => $iIblockId,
            'STATUS_CARD_XML_ID' => '',
            'STATUS_CARD' => '',

            'AMOUNT' => 0.0,
        ];
        if ($iMotivationId <= 0) {
            return $arResult;
        }

        $res = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $iIblockId,
                'CHECK_PERMISSIONS' => 'Y',
                'ID' => (int)$iMotivationId
            ],
            false,
            false,
            [
                'ID', 'NAME',
                'PREVIEW_TEXT',
                'PROPERTY_' . 'SELECTED_BE',
                'PROPERTY_' . 'F_MONTH',
                'PROPERTY_' . 'SELECTED_ART',
                'PROPERTY_' . 'ADDITIONAL_USERS',
                'PROPERTY_' . 'TO_USERS',
                'PROPERTY_' . 'F_YEAR',
                'PROPERTY_' . 'STATUS_CARD',
                'PROPERTY_' . 'AMOUNT',
            ]
        );
        while ($arElemnt = $res->Fetch()) {
            if (!$arResult['STATUS_CARD_XML_ID']) {
                $arResult['STATUS_CARD_XML_ID'] = $this->getXmlIdStatusById((int)$arElemnt['PROPERTY_STATUS_CARD_ENUM_ID']);
            }
            $arResult['ID'] = (int)$arElemnt['ID'];
            $arResult['PREVIEW_TEXT'] = (string)$arElemnt['PREVIEW_TEXT'];
            $arResult['SELECTED_BE'] = (int)$arElemnt['PROPERTY_' . 'SELECTED_BE' . '_VALUE'];
            $arResult['SELECTED_ART'] = (int)$arElemnt['PROPERTY_' . 'SELECTED_ART' . '_VALUE'];
            $arResult['F_MONTH'] = (int)$arElemnt['PROPERTY_' . 'F_MONTH' . '_ENUM_ID'];
            $arResult['F_MONTH_VALUE'] = (string)$arElemnt['PROPERTY_' . 'F_MONTH' . '_VALUE'];
            $arResult['F_YEAR'] = (int)$arElemnt['PROPERTY_' . 'F_YEAR' . '_VALUE'];
            $arResult['ADDITIONAL_USERS'][] = (int)$arElemnt['PROPERTY_' . 'ADDITIONAL_USERS' . '_VALUE'];
            $iToUser = (int)$arElemnt['PROPERTY_' . 'TO_USERS' . '_VALUE'];
            if ($iToUser && !in_array($iToUser, $arResult['TO_USERS'])) {
                $arResult['TO_USERS'][] = $iToUser;
            }
            $arResult['STATUS_CARD'] = $arElemnt['PROPERTY_' . 'STATUS_CARD' . '_VALUE'];
            $arResult['AMOUNT'] = (float)$arElemnt['PROPERTY_' . 'AMOUNT' . '_VALUE'];
        }
        // Баланс
        $arBalances = $this->getBalance(
            $arResult['SELECTED_BE'],
            $arResult['SELECTED_ART'],
            $arResult['F_MONTH_VALUE'],
            $arResult['F_YEAR']
        );
        $arResult['BALANCE_ACCUMULATIVE'] = (float)$arBalances['BALANCE_ACCUMULATIVE'] - (float)$arResult['AMOUNT'];
        $arResult['BALANCE_PLAN'] = (float)$arBalances['BALANCE_PLAN'];
        $arResult['BALANCE_FACT'] = (float)$arBalances['BALANCE_FACT'];
        $iNumberMonth = $arResult['I_MONTH'] = $this->getMonthNumber($arResult['F_MONTH_VALUE']);
        if ($iNumberMonth < 10) {
            $arResult['I_MONTH'] = '0' . strval($iNumberMonth);
        }
        return $arResult;
    }

    /**
     * Проверка может ли текущий пользователь редактировать
     * @return bool
     */
    private function canCreate(): bool
    {
        $obAccess = Access::getInstance();
        return $obAccess->isCanCreate()->isSuccess();
    }

    /**
     * Сохранение пользователя для премирования
     * @param int $iMotivationId
     * @param array $arFields
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function saveUserInStatement(int $iMotivationId, array $arFields)
    {
        // Выбор данных о ведомости из БД
        $arMotivationData = $this->getMotivationData($iMotivationId);
        // поля для всех пользователй одинаковые
        $arExPvFields = [
            'UF_IB_BP' => $arMotivationData['ID'],
            'UF_BE' => $arMotivationData['SELECTED_BE'],
            'UF_ARTICLE' => $arMotivationData['SELECTED_ART'],
            'UF_YEAR' => $arMotivationData['F_YEAR'],

            'UF_CURRENCY' => $this->getUfCurrency('RUB'),

            'UF_MONTH' => $this->getMonthUf($arMotivationData['F_MONTH']),
            'UF_ACCEPTED' => 0,
            'UF_DELETED' => 0,


        ];
        $arUsersInMotivationToAdd = [];
        /**
         * Группировака данных по пользователю в $arFields обязательно должен
         * быть такой массив
         * [envelope] => Array
         * (
         *      [awq1e] => 1
         *      [huqnx] => 2
         * )
         *
         * [users] => Array
         * (
         *      [awq1e] => 35
         *      [huqnx] => 7
         * )
         *
         * [sum_motivations] => Array
         * (
         *      [awq1e] => 350
         *      [huqnx] => 450
         * )
         */
        foreach ($arFields['envelope'] as $index => $iEnvelope) {
            $fSum = $arFields['sum_motivations'][$index];
            $fSum = str_replace(' ', '', $fSum);
            $fSum = str_replace(',', '.', $fSum);
            $arUsersInMotivationToAdd[] = array_merge(
                $arExPvFields,
                [
                    'UF_CON_NUM' => $iEnvelope,
                    'UF_USER' => $arFields['users'][$index],
                    'UF_SUM' => $fSum,
                ]
            );
        }
        // пользователи на обновление
        $arUsersInMotivationToUpdate = [];
        // пользователи на удаление
        $arUsersInMotivationToDelete = [];
        // получение пользователей из БД
        $arUsersInMotivation = $this->getUsersByMotivationId($arMotivationData['ID']);
        foreach ($arUsersInMotivation as $arUserInExPv) {
            foreach ($arUsersInMotivationToAdd as $key => $arUserByEnvelope) {
                // если совпадают номера конвертов
                if ($arUserByEnvelope['UF_CON_NUM'] == $arUserInExPv['UF_CON_NUM']) {
                    // в HL UF_SUM это тип деньги, и прилатют данные из БД "100|RUB"
                    // подготовка данных для вывода
                    if (strpos($arUserInExPv['UF_SUM'], '|RUB') !== false) {
                        $arUserByEnvelope['UF_SUM'] .= '|RUB';
                    }
                    // если все одинаково, то обновлять не надо
                    if ($arUserByEnvelope['UF_USER'] == $arUserInExPv['UF_USER']
                        && $arUserByEnvelope['UF_SUM'] == $arUserInExPv['UF_SUM']
                        && $arUserByEnvelope['UF_MONTH'] == $arUserInExPv['UF_MONTH']
                        && $arUserByEnvelope['UF_YEAR'] == $arUserInExPv['UF_YEAR']
                        && $arUserByEnvelope['UF_CURRENCY'] == $arUserInExPv['UF_CURRENCY']
                    ) {
                        unset($arUsersInMotivationToAdd[$key]);
                        continue 2;
                    } else {
                        $arUserInExPv['UF_USER'] = $arUserByEnvelope['UF_USER'];
                        $arUserInExPv['UF_SUM'] = $arUserByEnvelope['UF_SUM'];
                        $arUserInExPv['UF_MONTH'] = $arUserByEnvelope['UF_MONTH'];
                        $arUserInExPv['UF_YEAR'] = $arUserByEnvelope['UF_YEAR'];
                        $arUserInExPv['UF_CURRENCY'] = $arUserByEnvelope['UF_CURRENCY'];
                        // добавление на обновление
                        $arUsersInMotivationToUpdate[] = $arUserInExPv;
                        unset($arUsersInMotivationToAdd[$key]);
                        continue 2;
                    }
                }
            }
            // удаление
            $arUsersInMotivationToDelete[] = $arUserInExPv;
        }

        if ($arUsersInMotivationToAdd) {
            foreach ($arUsersInMotivationToAdd as $arUserByEnvelope) {
                $obResult = $this->getClassNameHlExpv()::add($arUserByEnvelope);
                if (!$obResult->isSuccess()) {
                    throw new Main\DB\Exception(
                        'user in motivation is bad on add',
                        implode(',', $obResult->getErrorMessages())
                    );
                }
            }
        }
        if ($arUsersInMotivationToUpdate) {
            foreach ($arUsersInMotivationToUpdate as $arUserByEnvelope) {
                $obResult = $this->getClassNameHlExpv()::update($arUserByEnvelope['ID'], $arUserByEnvelope);
                if (!$obResult->isSuccess()) {
                    throw new Main\DB\Exception(
                        'user in motivation is bad on update',
                        implode(',', $obResult->getErrorMessages())
                    );
                }
            }
        }
        if ($arUsersInMotivationToDelete) {
            foreach ($arUsersInMotivationToDelete as $arUserByEnvelope) {
                $obResult = $this->getClassNameHlExpv()::delete($arUserByEnvelope['ID']);
                if (!$obResult->isSuccess()) {
                    throw new Main\DB\Exception(
                        'user in motivation is bad on delete',
                        implode(',', $obResult->getErrorMessages())
                    );
                }
            }
        }
    }

    /**
     * Класс для работы с HL Expv
     * @return Main\ORM\Data\DataManager|string
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getClassNameHlExpv()
    {
        if ($this->entityClassHlExpv === null) {
            Main\Loader::includeModule('highloadblock');
            $hlblock = HL\HighloadBlockTable::getRow([
                'filter' => [
                    '=NAME' => 'Expv'
                ]
            ]);
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $this->entityClassHlExpv = $entity->getDataClass();
        }
        return $this->entityClassHlExpv;
    }

    /**
     * Получить пользователей в ведомости по id ведомости
     * @param int $iMotivationId
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getUsersByMotivationId(int $iMotivationId, bool $bHideUserName = false): array
    {
        $entityClass = $this->getClassNameHlExpv();
        $arResult = [];
        if ($iMotivationId <= 0) {
            return $arResult;
        }
        $resHl = $entityClass::getList([
            'filter' => [
                '=UF_IB_BP' => $iMotivationId
            ],
            'order' => [
                'UF_CON_NUM' => 'asc'
            ],
            'select' => [
                '*'
                , 'USER_NAME' => 'USER.NAME'
                , 'USER_LAST_NAME' => 'USER.LAST_NAME'
                , 'USER_SECOND_NAME' => 'USER.SECOND_NAME'
            ],
            'runtime' => [
                new Main\Entity\ReferenceField(
                    'USER',
                    Main\UserTable::class,
                    ['=this.UF_USER' => 'ref.ID'],
                )
            ]
        ]);
        while ($arUserInMotivation = $resHl->fetch()) {
            if ($bHideUserName) {
                $arUserInMotivation['FIO'] = '-- -- --';
            } else {
                $arUserInMotivation['FIO'] = trim(implode(
                    ' ',
                    [
                        $arUserInMotivation['USER_NAME'],
                        $arUserInMotivation['USER_SECOND_NAME'],
                        $arUserInMotivation['USER_LAST_NAME'],
                    ]
                ));
            }

            $arUserInMotivation['SUM_FORMATTED'] = floatval(explode('|', $arUserInMotivation['UF_SUM'])[0]);
            $arResult[] = $arUserInMotivation;
        }
        return $arResult;
    }

    /** Расчет суммы по ведомости (общая сумма по всем суммам сотрудников, указанных в ведомости)
     * @param int $iMotivationId
     * @return float
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function calculateStatementAmount(int $iMotivationId): float
    {
        $entityClass = $this->getClassNameHlExpv();
        $arResult = [];
        if ($iMotivationId <= 0) {
            return 0.0;
        }
        $resHl = $entityClass::getList([
            'filter' => [
                '=UF_IB_BP' => $iMotivationId
            ],
            'group' => [
                'UF_IB_BP'
            ],
            'select' => [
                'AMOUNT'
            ],
            'runtime' => [
                new Main\Entity\ExpressionField('AMOUNT', "SUM(REPLACE(UF_SUM, '|RUB', ''))"),
            ]
        ]);
        if ($arUserInMotivation = $resHl->fetch()) {
            $arResult = $arUserInMotivation;
        }
        return (float)$arResult['AMOUNT'];
    }

    /**
     * Сохранение суммы по ведомости, данные выибраются из БД
     * @param int $iMotivationId
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function saveStatementAmount(int $iMotivationId): void
    {
        $iIblockId = Manager::getIblockId('motivation');
        CIBlockElement::SetPropertyValuesEx($iMotivationId, $iIblockId, ['AMOUNT' => $this->calculateStatementAmount($iMotivationId)]);
    }

    /**
     * Согласующие пользователи выбираются из группы с кодом "approved_motivation"
     * @return array
     */
    private function getAdditionalUsers(): array
    {
        $arGroup = Main\GroupTable::getRow(
            [
                'filter' => ['=STRING_ID' => 'approved_motivation'],
                'select' => ['ID'],
            ]
        );
        $by = '';
        $order = '';
        $obResultUsers = CUser::GetList(
            $by,
            $order,
            [
                'ACTIVE' => 'Y',
                'GROUPS_ID' => [$arGroup['ID']],
            ],
            [
                'FIELDS' => [
                    'ID',
                    'NAME',
                    'SECOND_NAME',
                    'LAST_NAME',
                ]
            ]
        );
        $arResult = [];
        while ($arUser = $obResultUsers->Fetch()) {
            $arResult[] = $this->formatAdditionalUser($arUser);
        }
        return $arResult;
    }

    /**
     * Выбор согласующих пользователей из Ярлыка премирования
     * @param array $arUsersIds
     * @return array
     */
    private function getSelectedAdditionalUsers(array $arUsersIds): array
    {
        $arResult = [];
        foreach ($arUsersIds as $arUsersId) {
            if (!$arUsersId) {
                continue;
            }
            $arResult[$arUsersId] = [];
        }
        $arUsersIds[] = -1;
        $by = '';
        $order = '';
        $obResultUsers = CUser::GetList(
            $by,
            $order,
            [
                'ACTIVE' => 'Y',
                'ID' => implode('|', $arUsersIds),
            ],
            [
                'FIELDS' => [
                    'ID',
                    'NAME',
                    'SECOND_NAME',
                    'LAST_NAME',
                ]
            ]
        );
        if ($obResultUsers->SelectedRowsCount()) {
            while ($arUser = $obResultUsers->Fetch()) {
                $arResult[$arUser['ID']] = $this->formatAdditionalUser($arUser);
            }
            $arResult = array_values($arResult);
        }
        return $arResult;
    }

    /**
     * Формирует единый формат для согласующих пользователей
     * @param array $arUser
     * @return array
     */
    private function formatAdditionalUser(array $arUser): array
    {
        return [
            'id' => $arUser['ID'],
            'js_id' => 'user_approve-' . $arUser['ID'],
            'js_name' => 'ADDITIONAL_USERS[]',
            'entityId' => 'user_approve',
            'title' => trim(implode(
                ' ',
                [
                    $arUser['NAME'],
                    $arUser['SECOND_NAME'],
                    $arUser['LAST_NAME'],
                ]
            )),
            'tabs' => 'user-approve-tab',
        ];
    }

    /**
     * Определение UF_enum месяца по iblock_enum месяца
     * @param int $iEnumId
     * @return int
     */
    private function getMonthUf(int $iEnumId): int
    {
        $arEnumMonthProp = CIBlockPropertyEnum::GetByID($iEnumId);
        $sXmlId = $arEnumMonthProp['XML_ID'];
        $rsEnum = CUserFieldEnum::GetList([], ["USER_FIELD_NAME" => "UF_MONTH", "XML_ID" => $sXmlId]);
        $arEnum = $rsEnum->Fetch();
        return (int)$arEnum['ID'];
    }

    /**
     * Получение ID валюты по коду
     * @param string $sCodeCurrency
     * @return int
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getUfCurrency(string $sCodeCurrency): int
    {
        $iIblockId = Manager::getIblockId('currencies_ib');
        $resCurrency = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iIblockId, 'PROPERTY_' . 'KOD_VALYUTY' => $sCodeCurrency],
            false,
            false,
            ['ID']
        );
        $arCurrency = $resCurrency->Fetch();
        return $arCurrency['ID'];
    }

    /**
     * Получение балансов
     * @param int $beId
     * @param int $articleId
     * @param string $month
     * @param int $year
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getBalance(int $beId, int $articleId, string $month, int $year)
    {
        $arBudget = \Immo\Motivation\Budget::getBudgetsActual(
            $beId,
            $articleId,
            $this->getMonthNumber($month),
            $year,
            ['cumulativeTotal', 'plan', 'fact', 'month']
        );
        return [
            //Осталось доступных средств = Баланс НИ
            'BALANCE_ACCUMULATIVE' => (float)$arBudget['cumulativeTotal'],
            //Доступно за весь период = План
            'BALANCE_PLAN' => (float)$arBudget['plan'],
            //Всего истрачено за период = Факт
            'BALANCE_FACT' => (float)$arBudget['fact'],
        ];
    }

    /**
     * Порядоквый номер месяца
     * @param string $sMonthName
     * @return int
     */
    private function getMonthNumber(string $sMonthName): int
    {
        return (int)array_search($sMonthName, [
            5 => "Май",
            6 => "Июнь",
            7 => "Июль",
            8 => "Август",
            9 => "Сентябрь",
            10 => "Октябрь",
            11 => "Ноябрь",
            12 => "Декабрь",
            1 => "Январь",
            2 => "Февраль",
            3 => "Март",
            4 => "Апрель",
        ]);
    }

    /**
     * Установка правк для премрования ведомости
     * @param $id
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function setMotivationRight($id)
    {
        global $USER;
        $iIblockId = Manager::getIblockId('motivation');
        $arGroup = Main\GroupTable::getRow(
            [
                'filter' => ['=STRING_ID' => 'EMPLOYEES_s1'],
                'select' => ['ID'],
            ]
        );
        $this->setElementRights(
            $iIblockId,
            $id,
            [
                'G' . $arGroup['ID'] => 'deny',
                'U' . $USER->GetID() => 'limited_edit',
            ]
        );
    }

    /**
     * @throws Main\ArgumentException
     */
    private function checkFields(array $arFields)
    {
        // проверка бизнес еденицы, корректная прилетела или нет.
        $this->checkBusinessUnit($this->arResult['FORM']['SELECTED_BE'], $arFields['SELECTED_BE']);
        // проверка статьи расходов
        $this->checkArt($this->arResult['FORM']['ART'], $arFields['SELECTED_ART']);
        // проверка месяца
        $this->checkMonth($this->arResult['FORM']['MONTH'], $arFields['F_MONTH']);
        // проверка согласующих пользователей, если они есть, они должны быть в списке
        if ($arFields['ADDITIONAL_USERS']
            && is_array($arFields['ADDITIONAL_USERS'])
            && count($arFields['ADDITIONAL_USERS'])) {
            $this->checkAdditionalUsers($this->arResult['FORM']['ADDITIONAL_USERS'], $arFields['ADDITIONAL_USERS']);
        }
        if ($this->arResult['FORM']['FISCAL_YEAR'] != $arFields['F_YEAR']) {
            throw new Main\ArgumentException('Бюджетный год некорректный');
        }
        if ($this->arResult['FORM']['CURRENCY'] != $arFields['UF_CURRENCY']) {
            throw new Main\ArgumentException('Валюта некорректная');
        }
        // Проверка Сумма по ведомости, она не может превышать остаток доступны средств
        $this->checkBalanceAccumulative();
    }

    /**
     * Проверка бизенс еденицы
     * @param array $arAllBusinessUnits
     * @param int $iFindBusinessUnitId
     * @return void
     * @throws Main\ArgumentException
     */
    private function checkBusinessUnit(array $arAllBusinessUnits, int $iFindBusinessUnitId)
    {
        $this->checkFieldByListValues($arAllBusinessUnits, $iFindBusinessUnitId, 'id бизнес еденицы некорректный');
    }

    /**
     * Проверка статьи расходов
     * @param array $arArticles
     * @param int $iCurrentArticleId
     * @return void
     * @throws Main\ArgumentException
     */
    private function checkArt(array $arArticles, int $iCurrentArticleId)
    {
        $this->checkFieldByListValues($arArticles, $iCurrentArticleId, 'id статьи расходов некорректный');
    }

    /**
     * Проверка статьи расходов
     * @param array $arMonthList
     * @param int $iCurrentMonthId
     * @return void
     * @throws Main\ArgumentException
     */
    private function checkMonth(array $arMonthList, int $iCurrentMonthId)
    {
        $this->checkFieldByListValues($arMonthList, $iCurrentMonthId, 'id месяца некорректный');
    }

    private function checkAdditionalUsers(array $arAdditionalUsers, array $arCurrentAdditionalUsers)
    {
        foreach ($arCurrentAdditionalUsers as $iUserId) {
            $this->checkFieldByListValues(
                $arAdditionalUsers,
                $iUserId,
                $iUserId . ' пользователь не найден в списке',
                'id'
            );
        }

    }

    /**
     * Проверка $checkedValue, есть ли он в списке допустимых значаний
     * @param array $arListValues
     * @param $checkedValue
     * @param $sExceptionMessage
     * @param string $keyValue
     * @return bool
     * @throws Main\ArgumentException
     */
    private function checkFieldByListValues(array $arListValues, $checkedValue, $sExceptionMessage, string $keyValue = 'ID'): bool
    {
        $bIsValueCorrect = false;
        foreach ($arListValues as $arValue) {
            if ($arValue[$keyValue] == $checkedValue) {
                $bIsValueCorrect = true;
                break;
            }
        }
        if (!$bIsValueCorrect) {
            throw new Main\ArgumentException($sExceptionMessage);
        }
        return true;
    }

    /**
     * Проверка Сумма по ведомости, она не может превышать остаток доступны средств
     * @return void
     * @throws Main\ArgumentOutOfRangeException
     */
    private function checkBalanceAccumulative()
    {
        $fBalanceAccumulative = $this->arResult['BALANCE_ACCUMULATIVE'];
        if ($fBalanceAccumulative && $this->arResult['FORM']['USER_IN_MOTIVATION']) {
            $fSum = 0;
            $arUserId = [];
            foreach ($this->arResult['FORM']['USER_IN_MOTIVATION'] as $arUser) {
                $fSum += (float)$arUser['SUM_FORMATTED'];
                $iUserId = (int)$arUser['UF_USER'];
                if (in_array($iUserId, $arUserId)) {
                    throw new \Exception('Дублирование пользователей');
                } else {
                    $arUserId[] = $iUserId;
                }
            }
            if ($fSum > $fBalanceAccumulative) {
                throw new Main\ArgumentOutOfRangeException('Сумма по ведомости = ' . $fSum, 1, $fBalanceAccumulative);
            }
            if ($fSum <= 0) {
                throw new Main\ArgumentOutOfRangeException('Сумма по ведомости меньше или равна нулю', 1, $fBalanceAccumulative);
            }
        }
    }

    /**
     * Запуск БП
     * @param int $iMotivationId
     * @return Main\Result
     * @throws Main\Db\SqlQueryException
     */
    private function startWorkflow(int $iMotivationId): Main\Result
    {
        $obResultStartWorkflow = new Main\Result();
        global $USER;
        $arErrors = [];
        $fSumCost = 0.0;
        $obBudget = new Budget(
            $this->arResult['ID'],
            $this->arResult['SELECTED_BE'],
            $this->arResult['SELECTED_ART'],
            $this->arResult['AMOUNT'],
        );
        # Создать резерв
        $obResultCreateDistribution = $obBudget->createDistribution();
        if ($obResultCreateDistribution->isSuccess()) {
            # Резервирование
            $obResultCreateReserve = $obBudget->createReserve($this->arResult['I_MONTH'], $this->arResult['F_YEAR']);
            if ($obResultCreateReserve->isSuccess()) {
                $fSumCost = $obBudget->getSumCost();
                if (!$fSumCost) {
                    $obResultStartWorkflow->addError(
                        new Main\Error('sum cost is zero', 'sum_cost_zero')
                    );
                }
            } else {
                $obResultStartWorkflow->addError(
                    new Main\Error('create reserve is bad', 'create_reserve')
                );
            }
        } else {
            $obResultStartWorkflow->addError(
                new Main\Error('distribution was not created', 'distribution_create')
            );
        }
        if ($obResultStartWorkflow->isSuccess()) {
            CBPDocument::StartWorkflow(
                $this->getBpWorkflowTemplateByCode('MOTIVATION_INIT'),
                [
                    0 => 'lists',
                    1 => 'Bitrix\\Lists\\BizprocDocumentLists',
                    2 => $iMotivationId,
                ],
                [
                    'TargetUser' => 'user_' . $USER->GetID(),
                    'DocumentEventType' => 16,
                ],
                $arErrors
            );
        }
        return $obResultStartWorkflow;
    }

    /**
     * Если стартовый статусв
     * @return bool
     */
    private function isStartStatus(): bool
    {
        return $this->arResult['STATUS_CARD_XML_ID'] === 'START';
    }

    /**
     * НАчальный статус
     * @return int
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getStartStatusId(): int
    {
        $arStatus = $this->getStatusCard();
        foreach ($arStatus as $iIdStatus => $sXmlIdStatus) {
            if ($sXmlIdStatus === 'START') {
                return (int)$iIdStatus;
            }
        }
        return 0;
    }

    /**
     * Статусы ведомости
     * @return array<int, string>
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getStatusCard(): array
    {
        if ($this->arStatusCard === null) {
            $this->arStatusCard = [];
            $iIblockId = Manager::getIblockId('motivation');
            $arProps = Manager::getPropertyByCode('STATUS_CARD', $iIblockId);
            foreach ($arProps['VALUES'] as $arValue) {
                $this->arStatusCard[$arValue['ID']] = $arValue['XML_ID'];
            }
        }
        return $this->arStatusCard;
    }

    /**
     * Получить xml_id статуса, по enum_id статуса
     * @param int $iIdStatus
     * @return string|null
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getXmlIdStatusById(int $iIdStatus): ?string
    {
        $arStatus = $this->getStatusCard();
        if (array_key_exists($iIdStatus, $arStatus)) {
            return $arStatus[$iIdStatus];
        }
        return null;
    }

    /**
     * Получить id шаблон БП взависимости от статуса
     * @param string $sStatusCardXmlId
     * @return array
     */
    private function getBpWorkflowTemplateByStatusCard(string $sStatusCardXmlId): array
    {
        $arResult = [];
        $arResult[] = $this->getBpWorkflowTemplateByCode('MOTIVATION_' . $sStatusCardXmlId);
        if ($sStatusCardXmlId == 'MATCHING_FIN_DIR') {
            $arResult[] = $this->getBpWorkflowTemplateByCode('MOTIVATION_ADDITIONAL_MATCHING');
            $arResult[] = $this->getBpWorkflowTemplateByCode('MOTIVATION_ADDITIONAL_MATCHING_NEXT');
        }
        return $arResult;
    }

    private function getBpWorkflowTemplateByCode(string $mixedCode): int
    {
        static $arTmpBpWorkflowTemplate = [];
        if (!count($arTmpBpWorkflowTemplate)) {

            $iIblockId = Manager::getIblockId('motivation');
            $resWfTemp = WorkflowTemplateTable::getList([
                'filter' => [
                    '=DOCUMENT_TYPE' => 'iblock_' . $iIblockId,
                ],
                'select' => ['ID', 'SYSTEM_CODE']
            ]);
            while ($arTemplate = $resWfTemp->fetch()) {
                $key = $arTemplate['SYSTEM_CODE'] ?: $arTemplate['ID'];
                $arTmpBpWorkflowTemplate[$key] = $arTemplate['ID'];
            }
        }
        return (int)$arTmpBpWorkflowTemplate[$mixedCode];
    }

    /**
     * Генерация строики из пользователей, которые должны согласовать
     * @return void
     */
    private function initUserAgreeInLine(): void
    {
        if ($this->arResult['TO_USERS'] && is_array($this->arResult['TO_USERS'])) {
            $arTmpUsers = $this->getSelectedAdditionalUsers($this->arResult['TO_USERS']);
            $arTumUserName = [];
            foreach ($arTmpUsers as $arTmpUser) {
                $arTumUserName[] = $arTmpUser['title'];
            }
            $this->arResult['TO_USERS_IN_LINE'] = '(';
            $this->arResult['TO_USERS_IN_LINE'] .= implode(', ', $arTumUserName);
            $this->arResult['TO_USERS_IN_LINE'] .= ')';
        }
    }

    private function canEdit(int $iMotivationId): bool
    {
        $obAccess = Access::getInstance();
        return $obAccess->isCanEdit($iMotivationId);
    }

    /**
     * текущий пользователь бухгалтер?
     * @param int $iBusinessUnit
     * @param int $iIblockId
     * @return bool
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function bIsAccountant(int $iBusinessUnit, int $iIblockId): bool
    {
        $iBusinessUnitV2 = \Immo\Structure\Organization::defineBeComp($iBusinessUnit);
        $arUser = \Immo\Tools\ActivityTools::loadUsersForStage('Оплата', $iIblockId, $iBusinessUnitV2);
        global $USER;
        if (is_array($arUser) && in_array($USER->GetID(), $arUser)) {
            return true;
        }

        return false;
    }

}