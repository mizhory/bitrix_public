<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Lists\BizprocDocumentLists;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Immo\Statements\Access\User;
use Immo\Statements\Component\StatementsComponent;
use Immo\Statements\Data\Department;
use Immo\Statements\Data\HLBlock;
use Immo\Statements\Data\Iblock;
use Immo\Statements\Data\SalaryStatement;
use Immo\Statements\Details\SalaryStatementGrid;
use Immo\Statements\UserType\UserField;

Loader::includeModule('ui');
Loader::includeModule('immo.statements');

Extension::load(['ui.buttons']);

/**
// * @property HttpRequest $request
 */
class StatementsDetailsComponent extends StatementsComponent
{
    /**
     * @var HLBlock Свойство для работы с обёрткой над HL-блоком ЗПВ
     */
    private HLBlock $hl;

    /**
     * Сеттер HLBlock
     *
     * @param HLBlock $hl
     * @return StatementsDetailsComponent
     */
    public function setHl(HLBlock $hl): StatementsDetailsComponent
    {
        $this->hl = $hl;
        return $this;
    }

    /**
     * @var SalaryStatement Обёртка для работы с ИБ ЗПВ
     */
    private SalaryStatement $statement;

    /**
     * Сеттер SalaryStatement
     * @param SalaryStatement $statement
     * @return StatementsDetailsComponent
     */
    public function setStatement(SalaryStatement $statement): StatementsDetailsComponent
    {
        $this->statement = $statement;
        return $this;
    }

    /**
     * @var SalaryStatementGrid Класс для формирования параметров грида детальной страницы ЗПВ
     */
    private SalaryStatementGrid $salaryStatementGrid;

    /**
     * Сеттер SalaryStatementGrid
     *
     * @param SalaryStatementGrid $salaryStatementGrid
     * @return StatementsDetailsComponent
     */
    public function setSalaryStatementGrid(SalaryStatementGrid $salaryStatementGrid): StatementsDetailsComponent
    {
        $this->salaryStatementGrid = $salaryStatementGrid;
        return $this;
    }

    /**
     * Записывает id элемента ИБ ЗПВ в параметры компонента
     *
     * @param $arParams
     * @return $this
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareElementId(&$arParams): StatementsDetailsComponent
    {
        if(!is_null($this->request['rowId'])) {
            $rowId = $this->request['rowId'];
            $arParams['element_id'] = $this->hl->getLabelsSalaryElementId($rowId);
        } elseif (!is_null($this->request['elementId'])) {
            $arParams['element_id'] = (int) $this->request['elementId'];
        } elseif (!is_null($arParams['VARIABLES']['ID'])) {
            $arParams['element_id'] = (int) $arParams['VARIABLES']['ID'];
        }

        return $this;
    }

    /**
     * Записывает роль текущего пользователя в параметры компонента
     *
     * @param $arParams
     * @return $this
     */
    protected function prepareRole(&$arParams): StatementsDetailsComponent
    {
        if(!is_null($arParams['role'])) {
            return $this;
        }

        if(!is_null($arParams['VARIABLES']['ROLE'])) {
            $arParams['role'] = strtoupper($arParams['VARIABLES']['ROLE']);
        }

        if(!is_null($this->request['role'])) {
            $arParams['role'] = strtoupper($this->request['role']);
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param $arParams
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function onPrepareComponentParams($arParams): array
    {
        $this
            ->prepareRole($arParams)
            ->prepareHlEntityName($arParams)
            ->setHl(HLBlock::createInstance($arParams['hl_entity']))
            ->prepareElementId($arParams);

        return parent::onPrepareComponentParams($arParams);
    }

    /**
     * Получает сущность HL-блока по роли пользователя
     *
     * @param array $arParams
     *
     * @return $this
     *
     * @throws ArgumentNullException
     */
    protected function prepareHlEntityName(array &$arParams): StatementsDetailsComponent
    {
        if(!is_null($arParams['role'])) {
            $viewsByRole = User::$viewsByRole[$arParams['role']];
            $arParams['hl_entity'] = $viewsByRole['hl_entity'];

            return $this;
        }

        throw new ArgumentNullException('role');
    }

    /**
     * Формирует селектор для настройки типа расчёта итоговой суммы (представление Директор по БЕ)
     *
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    protected function prepareSelector(): StatementsDetailsComponent
    {
        $roles = $this->user->getSalaryStatementsRoles();

        $scValueId = $this->statement->getStatusCardValueId();
        $iblockId = Iblock::getIblockIdByElementId($this->arParams['element_id']);
        $scValues = Iblock::getPropertiesListValues('STATUS_CARD', $iblockId, $scValueId, true);

        if(
            in_array(self::ROLE_BE_DIRECTOR, $roles['roles'], true) &&
            $scValues[$scValueId]['xml_id'] === self::PROPERTY_STATUS_CARD_BE_DIRECTOR_XML_ID
        ) {
            $this->arResult['show_selector'] = true;
            $items = $this->hl->getFields(['FIELD_NAME' => 'UF_TOTAL_SUM_CALCULATION_TYPE']);


            foreach ($items['items'] as $itemId => $item) {
                $this->arResult['selector_items'][$itemId] = $item['value'];
            }
        }

        return $this;
    }

    /**
     * Формирует фильтр для получения элементов отдельной ЗПВ
     *
     * @return array
     */
    protected function prepareFilter()
    {
        $filter = [];

        foreach (User::$fieldsForFilter as $item) {
            if(in_array($this->arParams['role'], $item['roles'], true)) {
                foreach ($item['fields'] as $option => $hlEntityField) {
                    $filter[$hlEntityField] = $this->arParams[$option];
                }
            }
        }

        return $filter;
    }

    /**
     * Возвращает ID грида
     *
     * @return string
     */
    protected function getGridId()
    {
        return 'statements_list';
    }

    /**
     * Формирует тулбар
     */
    protected function makeToolbar(): StatementsDetailsComponent
    {
        Toolbar::addButton([
            'text' => 'Выгрузить в excel',
            'color' => Color::SECONDARY
        ]);

        Toolbar::addButton([
            'link' => '/sheets/salary/',
            'text' => 'К списку ведомостей',
            'color' => Color::LIGHT_BORDER
        ]);

        return $this;
    }

    /**
     * формирует массив параметров для грида
     */
    protected function makeGrid(): StatementsDetailsComponent
    {
        $filter = $this->prepareFilter();

        $gridOptions = new Options($this->getGridId());
        $sort = $gridOptions->getSorting();

        $this->arResult['GRID'] = [
            'GRID_ID' => $this->getGridId(),
            'HEADERS' => $this->salaryStatementGrid->getColumns(),
            'ROWS' => $this->salaryStatementGrid->getRows($filter, $sort['sort']),
            'SHOW_ROW_CHECKBOXES' => false,
        ];

        return $this;
    }

    /**
     * Обновляет элемент HL-блока
     *
     * @param $rowId
     * @param $field
     * @param $value
     *
     * @return array|bool
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function updateHlElementAction($rowId, $field, $value)
    {
        $this->reloadComponent();
        $entity = $this->hl->getEntity();

        $updParams = [
            $field => $value
        ];

        $update = $entity::update($rowId, $updParams);

        return $update->isSuccess() ?: $update->getErrorMessages();
    }

    /**
     * Формирует $arResult
     *
     * @inheritDoc
     */
    public function prepareResult(): StatementsDetailsComponent
    {
        $this
            ->setSalaryStatementGrid(
                new SalaryStatementGrid($this->hl, $this->arParams['element_id'], $this->arParams['role'])
            )
            ->setStatement(
                new SalaryStatement($this->arParams['element_id'])
            );

        if(
            !array_key_exists('be_id', $this->arParams) &&
            !array_key_exists('company_id', $this->arParams) &&
            !array_key_exists('status_card_value_id', $this->arParams)
        ) {
            $this->prepareElementData();
        }

        $this
            ->prepareWorkflowId()
            ->prepareSelector()
            ->prepareViewMode()
            ->prepareGeneralTotalSumCalculationValue()
            ->prepareJsOptions()

            ->makeToolbar()
            ->makeGrid();

        return $this;
    }

    /**
     * Записывает в $arResult id запущенного БП
     * @return $this
     */
    protected function prepareWorkflowId(): StatementsDetailsComponent
    {
        $this->arResult['workflow_id'] = $this->statement->getWorkflowId();
        return $this;
    }

    /**
     * Записывает в параметры компонента данные из элемента ИБ ЗПВ
     *
     * @return $this
     */
    protected function prepareElementData(): StatementsDetailsComponent
    {
        $this->arParams['be_id'] = $this->statement->getBeId();
        $this->arParams['company_id'] = $this->statement->getCompanyId();
        $this->arParams['status_card_value_id'] = $this->statement->getStatusCardValueId();

        return $this;
    }

    /**
     * Возвращает страницу из параметров
     * @return mixed
     */
    protected function getPage()
    {
        return $this->arParams['PAGE'];
    }

    /**
     * Возвращает ключ переменной из параметра VARIABLES
     * @return string
     */
    protected function getVariableId(): string
    {
        return sprintf('%s_ID',strtoupper($this->getPage()));
    }

    /**
     * Возвращает значение переменной из параметра VARIABLES
     * @return mixed
     */
    protected function getVariableValueId()
    {
        return $this->arParams['VARIABLES'][$this->getVariableId()];
    }

    /**
     * Формирует параметры отображения (БЕ/ЮЛ, валюта, статус согласования и т.д.)
     *
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     */
    protected function prepareViewMode(): StatementsDetailsComponent
    {
        $iblockId = Iblock::getIblockId(self::IBLOCK_CODE_LABELS_SALARY, self::IBLOCK_TYPE_BITRIX_PROCESSES);
        $currencyLabel = UserField::getLabel('UF_CURRENCY', Department::getUfEntityId());
        $scValueId = $this->arParams['status_card_value_id'];
        $statusCard = Iblock::getPropertiesListValues('STATUS_CARD', $iblockId, $scValueId);
        $viewsByRole = User::$viewsByRole;

        $unitTypePropertyCode = $viewsByRole[$this->arParams['role']]['iblock_property'];
        $unitTypePropertyId = $viewsByRole[$this->arParams['role']]['details_view_option'];

        $this->arResult['views'] = [
            [
                'label' => Iblock::getPropertyName($unitTypePropertyCode, $iblockId),
                'value' => Department::getElementNameById($this->arParams[$unitTypePropertyId])
            ],
            [
                'label' => $currencyLabel,
                'value' => Department::getDepartmentCurrency($this->arParams['be_id'])
            ],
            [
                'label' => Iblock::getPropertyName('STATUS_CARD', $iblockId),
                'value' => $statusCard[$scValueId]
            ]
        ];

        $this->arResult['is_admin'] = CurrentUser::get()->isAdmin();

        return $this;
    }

    /**
     * Формирует параметры для передачи в js
     * @return $this
     */
    protected function prepareJsOptions(): StatementsDetailsComponent
    {
        $jsOptions = [
            'grid_id' => $this->getGridId(),
            'is_admin' => $this->arResult['is_admin'],
            'role' => $this->arParams['role'],
            'element_id' => $this->arParams['element_id']
        ];

        $this->arResult['js_options'] = CUtil::PhpToJSObject($jsOptions);
        return $this;
    }

    /**
     * Передаёт в $arResult id значения типа расчёта итоговой суммы, если оно задано
     *
     * @return $this
     */
    protected function prepareGeneralTotalSumCalculationValue(): StatementsDetailsComponent
    {
        $this->arResult['total_sum_calculation_type_id'] = $this->statement->getTotalSumCalculationTypeId();
        return $this;
    }

    /**
     * Возвращает компонент журнала БП для открытия в слайдере
     *
     * @return Component
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getBizprocLogAction(): Component
    {
        $this->reloadComponent();

        return new Component('bitrix:bizproc.log', 'modern', [
            'MODULE_ID' => 'lists',
            'ENTITY' => BizprocDocumentLists::class,
            'COMPONENT_VERSION' => 2,
            'ID' => $this->statement->getWorkflowId()
        ]);
    }

    /**
     * Экшен для обновления тип расчёта итоговой суммы для всех HL-элементов ЗПВ после изменения аналогичного параметра в элементе ИБ ЗПВ
     *
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws SystemException
     */
    public function updateGeneralTotalSumCalculationAction($elementId, $value): array
    {
        $this->reloadComponent();
        $elementsOptions = [
            'filter' => ['UF_LABELS_SALARY_ELEMENT_ID' => $elementId]
        ];

        $elements = $this->hl->getElements($elementsOptions);

        CIBlockElement::SetPropertyValuesEx($elementId, null, [
            'TOTAL_SUM_CALCULATION_TYPE' => $value
        ]);

        $this->statement->reloadElementData();

        $options = ['UF_TOTAL_SUM_CALCULATION_TYPE' => $value];

        $update = $this->hl->getEntity()::updateMulti(array_column($elements, 'ID'), $options);
        $element = $this->statement->getElement();

        return [
            'update_hl_elements' => $update->isSuccess(),
            'update_salary_statement_element' => $element['total_sum_calculation_type_id'] === (int) $value
        ];
    }

    /**
     * Загружает заново параметры компонента для использования в контроллере
     * @return $this
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function reloadComponent(): StatementsDetailsComponent
    {
        $this->onPrepareComponentParams($this->arParams);
        $this->prepareResult();

        return $this;
    }
}