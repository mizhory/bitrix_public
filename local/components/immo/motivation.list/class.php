<?php

namespace Immo\Components;

use CBitrixComponent;
use CIBlockElement;
use Bitrix\Main;
use CUser;
use Immo\Iblock\Manager;
use Immo\Motivation\ArticleExpenses;
use Immo\Motivation\BusinessUnits;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @description Отображение Премиальной ведомости
 */
class MotivationList extends CBitrixComponent
{
    /**
     * motivation iblock_id
     * @var int
     */
    private int $iIblockMotivation;

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->iIblockMotivation = (int)Manager::getIblockId('motivation');
    }

    /**
     * @var Main\Grid\Options
     */
    private Main\Grid\Options $obGrid;

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->arResult['FILTER_ID'] = 'motivation_list';
        $this->arResult['GRID_ID'] = 'motivation_list';
        $this->obGrid = new Main\Grid\Options($this->arResult['GRID_ID']);
        $this->arResult['DEFAULT_PAGE_SIZE'] = 20;
        $sTemplate = null;
        if ($this->request->getQuery('DOWNLOAD') === 'Y') {
            $sTemplate = 'download';
            $this->arResult['DEFAULT_PAGE_SIZE'] = 10000000;
        }
        $this->initColumnMotivationData();
        $this->initFilter();
        $this->getMotivationData();

        $this->includeComponentTemplate($sTemplate);
    }

    /**
     * Получение данных по ведомости из БД
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     */
    private function getMotivationData(): void
    {
        $sort = $this->obGrid->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
        $arNavParams = $this->obGrid->GetNavParams(['nPageSize' => $this->arResult['DEFAULT_PAGE_SIZE']]);
        $obNavigation = new Main\UI\PageNavigation($this->arResult['GRID_ID']);
        $obNavigation->allowAllRecords(true)->setPageSize($arNavParams['nPageSize'])->initFromUri();
        $arFilter = [
            'IBLOCK_ID' => $this->iIblockMotivation,
            'CHECK_PERMISSIONS' => 'Y',
            'ACTIVE' => 'Y'
        ];
        $filterOption = new Main\UI\Filter\Options($this->arResult['GRID_ID']);
        $filterData = $filterOption->getFilter();
        $arFilter = array_merge($arFilter, $this->prepareFilter('AMOUNT', $filterData));
        $arFilter = array_merge($arFilter, $this->prepareFilter('PAY_DATE', $filterData));
        foreach ($filterData as $k => $v) {
            if (strpos($k, 'PROPERTY_') === false) {
                continue;
            }
            $arFilter['=' . $k] = $v;
        }
        //Предварительный план согласования
        $resMotivation = CIBlockElement::GetList(
            $sort['sort'],
            $arFilter,
            false,
            $arNavParams,
            [
                'ID',
                'NAME',
                'PROPERTY_' . 'SELECTED_BE',
                'PROPERTY_' . 'SELECTED_BE' . '.NAME',
                'PROPERTY_' . 'SELECTED_ART' . '.NAME',
                'PROPERTY_' . 'STATUS_CARD',
                'PROPERTY_' . 'F_MONTH',
                'PROPERTY_' . 'F_YEAR',
                'PROPERTY_' . 'AMOUNT',
                'PROPERTY_' . 'PAY_DATE',
                'PROPERTY_' . 'ACCEPT_PLAN',
            ]
        );
        $obNavigation->setRecordCount($resMotivation->selectedRowsCount());
        $this->arResult['MOTIVATION_LIST'] = [];
        $arFilterForProps = [];
        while ($arMotivation = $resMotivation->Fetch()) {
            $arFilterForProps['ID'][] = $arMotivation['ID'];
            $this->arResult['MOTIVATION_LIST'][$arMotivation['ID']] = [
                'data' => [
                    //Данные ячеек
                    "ID" => $arMotivation['ID'],
                    "NAME" => $arMotivation['NAME'],
                    "SELECTED_BE" => $arMotivation["PROPERTY_SELECTED_BE_NAME"],
                    "SELECTED_ART" => $arMotivation["PROPERTY_SELECTED_ART_NAME"],
                    "STATUS_CARD" => $arMotivation["PROPERTY_STATUS_CARD_VALUE"],
                    "F_MONTH" => $arMotivation["PROPERTY_F_MONTH_VALUE"],
                    "F_YEAR" => $arMotivation["PROPERTY_F_YEAR_VALUE"],
                    "ASSIGNED_BY" => [],
                    "AMOUNT" => $arMotivation["PROPERTY_AMOUNT_VALUE"],
                    "PAY_DATE" => $arMotivation["PROPERTY_PAY_DATE_VALUE"] ?: '--.--.----',
                    "ACCEPT_PLAN" => $arMotivation["PROPERTY_ACCEPT_PLAN_VALUE"]['TEXT'] ?: '',
                    "TO_USERS" => [],
                    "DONE_USERS" => [],

                ],
                'columns' => [
                    'TO_USERS' => '',
                    'DONE_USERS' => '',
                    'ASSIGNED_BY' => '',
                    'NAME' => '<a href="/sheets/motivation/' . $arMotivation['ID'] . '/">'.$arMotivation['NAME'] . '</a>',
                ],
                'actions' => [
                    [
                        'DEFAULT' => true,
                        'text' => 'Открыть',
                        'onclick' => 'document.location.href="/sheets/motivation/' . $arMotivation['ID'] . '/"'
                    ],
                ],
                'PROPERTIES' => []
            ];
        }
        CIBlockElement::GetPropertyValuesArray(
            $this->arResult['MOTIVATION_LIST'],
            $arFilter['IBLOCK_ID'],
            ['ID' => $arFilterForProps['ID']],
            ['CODE' => ['TO_USERS', 'DONE_USERS','ASSIGNED_BY']],
            [
                'GET_RAW_DATA' => 'Y',
                'PROPERTY_FIELDS' => ['VALUE', 'ID', 'CODE']
            ]
        );
        $this->prepareUsers();
        $this->arResult['NAV'] = $resMotivation;
    }

    /**
     * Столбцы премиальной ведомости
     * @return void
     */
    private function initColumnMotivationData(): void
    {
        $this->arResult['COLUMNS'] = [
            ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
            ['id' => 'NAME', 'name' => 'Название', 'sort' => 'NAME', 'default' => true],
            ['id' => 'SELECTED_BE', 'name' => 'БЕ', 'sort' => 'PROPERTY_SELECTED_BE', 'default' => true],
            ['id' => 'SELECTED_ART', 'name' => 'Статья расходов', 'sort' => 'PROPERTY_SELECTED_ART', 'default' => true],
            ['id' => 'STATUS_CARD', 'name' => 'Статус', 'sort' => 'PROPERTY_STATUS_CARD', 'default' => true],
            ['id' => 'F_MONTH', 'name' => 'Месяц', 'sort' => 'PROPERTY_F_MONTH', 'default' => true],
            ['id' => 'F_YEAR', 'name' => 'Год', 'sort' => 'PROPERTY_F_YEAR', 'default' => true],
            ['id' => 'ASSIGNED_BY', 'name' => 'Ответственный', 'default' => true],
            ['id' => 'AMOUNT', 'name' => 'Сумма по ведомости', 'sort' => 'PROPERTY_AMOUNT', 'default' => true],
            ['id' => 'PAY_DATE', 'name' => 'Дата оплаты', 'sort' => 'PROPERTY_PAY_DATE', 'default' => true],
            ['id' => 'ACCEPT_PLAN', 'name' => 'Предварительный план согласования', 'default' => true],
            ['id' => 'TO_USERS', 'name' => 'На согласовании у', 'default' => true],
            ['id' => 'DONE_USERS', 'name' => 'Согласовавшие сотрудники', 'default' => true],
        ];
    }

    private function initFilter()
    {
        $this->arResult['FILTER'] = [
            [
                'id' => 'PROPERTY_' . 'SELECTED_BE',
                'name' => 'БЕ',
                'type' => 'list',
                'items' => $this->getBusinessUnits(),
                'params' => ['multiple' => 'Y'],
                'default' => true,
            ],
            [
                'id' => 'PROPERTY_' . 'SELECTED_ART',
                'name' => 'Статьи расходов',
                'type' => 'list',
                'items' => $this->getArticleExpenses(),
                'params' => ['multiple' => 'Y'],
                'default' => true,
            ],
            [
                'id' => 'PROPERTY_' . 'STATUS_CARD',
                'name' => 'Статус',
                'type' => 'list',
                'items' => $this->getStatus(),
                'params' => ['multiple' => 'Y'],
                'default' => true,
            ],
            [
                'id' => 'PROPERTY_' . 'F_MONTH',
                'name' => 'Месяц',
                'type' => 'list',
                'items' => $this->getMonth(),
                'params' => ['multiple' => 'Y'],
                'default' => true,
            ],
            [
                'id' => 'PROPERTY_' . 'F_YEAR',
                'name' => 'Год',
                'type' => 'list',
                'items' => $this->getYear(),
                'params' => ['multiple' => 'Y'],
                'default' => true,
            ],
            ['id' => 'AMOUNT', 'name' => 'Сумма по ведомости', 'type' => 'number', 'default' => true,],
            ['id' => 'PAY_DATE', 'name' => 'Дата оплаты', 'type' => 'date', 'default' => true,],
        ];
    }

    /**
     * Список БЕ для пользователя
     * @return string[]
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getBusinessUnits(): array
    {
        $obBusinessUnits = new BusinessUnits();
        $arBusinessUnits = $obBusinessUnits->availableBusinessUnitsForCurrenUser();
        $arResult = [];
        foreach ($arBusinessUnits as $arBusinessUnit) {
            $arResult[$arBusinessUnit['ID']] = $arBusinessUnit['NAME'];
        }
        return $arResult;
    }

    /**
     * Список БЕ для пользователя
     * @return string[]
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    private function getArticleExpenses(): array
    {
        $obArticles = new ArticleExpenses();
        $arArticlesExpenses = $obArticles->getArticles();
        $arResult = [];
        foreach ($arArticlesExpenses as $arArticleExpense) {
            $arResult[$arArticleExpense['ID']] = $arArticleExpense['NAME'];
        }
        return $arResult;
    }

    /**
     * Список статусов для фильтра
     * @return string[]
     */
    private function getStatus(): array
    {
        $arProps = Manager::getPropertyByCode('STATUS_CARD', $this->iIblockMotivation);
        $arResult = [];
        foreach ($arProps['VALUES'] as $arValue) {
            $arResult[(int)$arValue['ID']] = $arValue['VALUE'];
        }
        return $arResult;
    }

    /**
     * Список месяцев
     * @return string[]
     */
    private function getMonth(): array
    {
        $arProps = Manager::getPropertyByCode('F_MONTH', $this->iIblockMotivation);
        $arResult = [];
        // нужны только отмеченные Y
        foreach ($arProps['VALUES'] as $arValue) {
            $arResult[(int)$arValue['ID']] = $arValue['VALUE'];
        }
        return $arResult;
    }

    /**
     * Список месяцев
     * @return string[]
     */
    private function getYear(): array
    {
        $res = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $this->iIblockMotivation,
                'ACTIVE' => 'Y',
            ],
            ['PROPERTY_' . 'F_YEAR']
        );
        $arResult = [];
        while ($ar = $res->Fetch()) {
            $arResult[$ar['PROPERTY_F_YEAR_VALUE']] = $ar['PROPERTY_F_YEAR_VALUE'];
        }
        return $arResult;
    }

    /**
     * @param string $sCodeProp
     * @param array $filterData
     * @return array
     * @throws Main\ObjectException
     */
    private function prepareFilter(string $sCodeProp, array $filterData): array
    {
        $arPreFilter = [];
        if (array_key_exists($sCodeProp . '_numsel', $filterData)) {
            switch ($filterData[$sCodeProp . '_numsel']) {
                case 'more':
                    $sPref = '>=';
                    $arPreFilter[$sPref . 'PROPERTY_' . $sCodeProp] = floatval($filterData[$sCodeProp . '_from']);
                    break;
                case 'less':
                    $sPref = '<=';
                    $arPreFilter[$sPref . 'PROPERTY_' . $sCodeProp] = floatval($filterData[$sCodeProp . '_to']);
                    break;
                case 'exact':
                    $sPref = '=';
                    $arPreFilter[$sPref . 'PROPERTY_' . $sCodeProp] = floatval($filterData[$sCodeProp . '_from']);
                    break;
                case 'range':
                    $sPref = '><';
                    $arPreFilter[$sPref . 'PROPERTY_' . $sCodeProp] = [$filterData[$sCodeProp . '_from'], $filterData[$sCodeProp . '_to']];
                    break;
            }
        } elseif (array_key_exists($sCodeProp . '_datesel', $filterData)) {
            $sPref = '><';
            $obTimeFrom = new Main\Type\DateTime($filterData[$sCodeProp . '_from']);
            $obTimeTo = new Main\Type\DateTime($filterData[$sCodeProp . '_to']);
            $arPreFilter[$sPref . 'PROPERTY_' . $sCodeProp] = [$obTimeFrom->format('Y-m-d H:i:s'), $obTimeTo->format('Y-m-d H:i:s')];
        }
        return $arPreFilter;
    }

    /**
     * Подстановка пользователей в список
     * @return void
     */
    private function prepareUsers(): void
    {
        $arUserIds = [];
        foreach ($this->arResult['MOTIVATION_LIST'] as &$arMotivation) {
            if (array_key_exists('TO_USERS', $arMotivation['PROPERTIES'])
                && is_array($arMotivation['PROPERTIES']['TO_USERS']['VALUE'])) {
                $arUserIds = array_merge($arUserIds, $arMotivation['PROPERTIES']['TO_USERS']['VALUE']);
                $arMotivation['data']['TO_USERS'] = $arMotivation['PROPERTIES']['TO_USERS']['VALUE'];
            }
            if (array_key_exists('DONE_USERS', $arMotivation['PROPERTIES'])
                && is_array($arMotivation['PROPERTIES']['DONE_USERS']['VALUE'])) {
                $arUserIds = array_merge($arUserIds, $arMotivation['PROPERTIES']['DONE_USERS']['VALUE']);
                $arMotivation['data']['DONE_USERS'] = $arMotivation['PROPERTIES']['DONE_USERS']['VALUE'];
            }
            if (array_key_exists('ASSIGNED_BY', $arMotivation['PROPERTIES'])
                && $arMotivation['PROPERTIES']['ASSIGNED_BY']['VALUE']) {
                $arUserIds = array_merge($arUserIds, [$arMotivation['PROPERTIES']['ASSIGNED_BY']['VALUE']]);
                $arMotivation['data']['ASSIGNED_BY'] = [$arMotivation['PROPERTIES']['ASSIGNED_BY']['VALUE']];
            }
        }
        $arUsers = $this->findUsersByIds((array)$arUserIds);

        foreach ($this->arResult['MOTIVATION_LIST'] as &$arMotivation) {
            if ($arMotivation['data']['TO_USERS']) {
                foreach ($arMotivation['data']['TO_USERS'] as $arUserId) {
                    $arMotivation['columns']['TO_USERS'] .= $arUsers[$arUserId]['USER_NAME'];
                }
            }
            if ($arMotivation['data']['DONE_USERS']) {
                foreach ($arMotivation['data']['DONE_USERS'] as $arUserId) {
                    $arMotivation['columns']['DONE_USERS'] .= $arUsers[$arUserId]['USER_NAME'];
                }
            }
            if ($arMotivation['data']['ASSIGNED_BY']) {
                foreach ($arMotivation['data']['ASSIGNED_BY'] as $arUserId) {
                    $arMotivation['columns']['ASSIGNED_BY'] .= $arUsers[$arUserId]['USER_NAME'];
                }
            }
        }
    }

    /**
     * Поиск пользователй
     * @param array $arUsersIds
     * @return array
     */
    private function findUsersByIds(array $arUsersIds) : array
    {
        if (!$arUsersIds) {
            return [];
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
                    'EMAIL',
                ]
            ]
        );
        $arResult = [];
        while ($arUser = $obResultUsers->Fetch()) {
            $arResult[$arUser['ID']] = [
                'ID' => $arUser['ID'],
                'USER_NAME' => trim(implode(
                    ' ',
                    [
                        '(' . $arUser['EMAIL'] . ')',
                        $arUser['NAME'],
                        $arUser['SECOND_NAME'],
                        $arUser['LAST_NAME'],
                        '[<a href="/company/personal/user/' . $arUser['ID'] . '/">' . $arUser['ID'] . '</a>]<br />'
                    ]
                ))
            ];
        }
        return $arResult;
    }
}