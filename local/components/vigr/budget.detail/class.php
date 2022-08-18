<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CBitrixComponent::includeComponentClass("vigr:budget.all");

/**
 * Class CBudgetDetail
 * детальная страница бюджета
 */
class CBudgetDetail extends CBudgetAll
{
    public function executeComponent()
    {
        CModule::includeModule('vigr.budget');
        $this->buildData();
        $this->IncludeComponentTemplate();
    }

    public function buildTableParams()
    {
        global $USER;
        $dbArticles = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('articles')
            ]
        );

        $arArticles = [];

        while ($arArticle = $dbArticles->fetch()) {
            $arArticles[$arArticle['ID']] = $arArticle['NAME'];
        }
        if(!$this->arParams['beId']){
            throw new Exception();
        }

        $this->arResult['filterId'] = 'detail_budget_'.$USER->getId().'_'.$this->arParams['beId'];

        $nameBe = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('be'),
                'ID' => $this->arParams['beId']
            ]
        )->fetch()['NAME'];

        if(empty($arArticles)){
            throw new Exception('empty 48 detail');
        }
        $arBeS = [];

        $year = 2020;

        $arYearsItems = [
            '2020' => "2020 "
        ];

        while($year < 2050){
            $year++;
            $arYearsItems[$year] = "{$year} ";
        }

        $this->arResult['nameBe'] = $nameBe;

        $this->arResult['BUDGET_ROWS'] = [
            'HIDE' => [
                'plan' => 'План'
            ],
            'VISIBLE' => [
                'fact'=>'Факт',
                'saldo'=>'Сальдо',
                'inReserve'=>'Заявлено',
                'total'=>'Баланс',
                'cumulativeTotal'=>'Баланс НИ',
            ]
        ];

        $this->arResult['filterFields'] = [
            [
                'id' => 'year',
                'name' => 'Финансовый год',
                'type' => 'list',
                'items' => $arYearsItems,
                'params' => ['multiple' => 'Y']
            ],
            [
                'id' => 'article',
                'name' => 'Статья',
                'type' => 'list',
                'items' => $arArticles,
                'params' => ['multiple' => 'Y'],
            ],
            [
                'id' => 'budget',
                'name' => 'Выбор строчек бюджета',
                'type' => 'list',
                'items' => $this->arResult['BUDGET_ROWS']['VISIBLE'],
                'params' => ['multiple' => 'Y']
            ],
            [
                'id' => 'hideZero',
                'name' => 'Отображать нулевые статьи',
                'type' => 'checkbox',
            ]
        ];

        $this->arResult['filterId'] = 'detail_budget_'.$USER->getId().'_'.$this->arParams['beId'];
        $filterOption = new Bitrix\Main\UI\Filter\Options('detail_budget_'.$USER->getId().'_'.$this->arParams['beId']);
        $this->filterData = $filterOption->getFilter($this->arResult['filterFields']);

        $this->filterData['biznesUnitName'] = $this->arParams['beId'];

        $this->additionalTableParams =  'article';
    }
}