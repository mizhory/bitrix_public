<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Class CBudgetAll
 * Список всех бюджетов
 */
class CBudgetAll extends CBitrixComponent
{
    protected $filterData = [];

    protected $filterId = '';

    protected $additionalTableParams = '';

    public function executeComponent()
    {
        CModule::includeModule('vigr.budget');
        $this->buildData();
        $this->IncludeComponentTemplate();
    }


    /**
     * @throws Exception
     * @description Построение параметров в том числе фильтр
     */
    public function buildTableParams()
    {
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

        $dbBeS = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('be')
            ]
        );

        $arBeS = [];

        while ($arBe = $dbBeS->fetch()) {
            $arBeS[$arBe['ID']] = $arBe['NAME'];
        }

        $year = 2020;

        $arYearsItems = ['2020'=>2020];

        while($year < 2050){
            $year++;
            $arYearsItems[$year] = $year;
        }

        if(empty($arArticles) || empty($arBeS)){
            throw new Exception('error!');
        }

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
                'id' => 'article',
                'name' => 'Статья',
                'type' => 'list',
                'items' => $arArticles,
                'params' => ['multiple' => 'Y'],
            ],
            [
                'id' => 'biznesUnitName',
                'name' => 'БЕ',
                'type' => 'list',
                'items' => $arBeS,
                'params' => ['multiple' => 'Y'],
            ],
            [
                'id' => 'year',
                'name' => 'Финансовый год',
                'type' => 'list',
                'items' =>$arYearsItems,
                'params' => ['multiple' => 'Y']
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



        global $USER;

        $this->arResult['filterId'] = 'all_budget_' . $USER->getId();
        $filterOption = new Bitrix\Main\UI\Filter\Options('all_budget_' . $USER->getId());
        $this->filterData = $filterOption->getFilter($this->arResult['filterFields']);
    }

    /**
     * @throws Exception
     * Получение данных для таблицы
     */
    public function buildData()
    {
        $this->buildTableParams();

        $filterData = $this->filterData;

        $this->arResult['length'] = count($filterData['budget']) + 1;
        $this->filterData = $filterData;

        $this->arResult['filterData'] = $this->filterData;

        $query = \Vigr\Budget\Internals\BudgetTable::query();

        if (!empty($filterData['article']) and is_array($filterData['article'])) {
            $query->whereIn('article', $filterData['article']);
        }
        if (!empty($filterData['biznesUnitName'])) {
            if (is_array($filterData['biznesUnitName'])) {
                $query->whereIn('biznesUnit', $filterData['biznesUnitName']);
            } else {
                $query->where('biznesUnit', $filterData['biznesUnitName']);
            }
        }
        if (!empty($filterData['year']) and is_array($filterData['year'])) {
            $query->whereIn('year', $filterData['year']);
        }
        if ($filterData['hideZero'] == 'N') {
            $query->whereNot('total', 0);
        }

        if($filterData['FIND'] != ''){
            $filter = \Bitrix\Main\ORM\Query\Query::filter()->logic('or');
            $arFields = \Vigr\Budget\Internals\BudgetTable::getMap();

            foreach ($arFields as $arField){
                $name = $arField->getName();
                if($name === 'id' || $name === 'unicHash' || $name === 'biznesUnit' || $name === 'article'){
                    continue;
                }
                if(get_class($arField) === 'Bitrix\Main\ORM\Fields\StringField'){
                    $filter->whereLike($name, "%{$filterData['FIND']}%");
                }elseif(is_numeric($filterData['FIND'])){
                    $filter->where($name, $filterData['FIND']);
                }
            }

            if (!empty($filter->getConditions())) {
                $query->where($filter);
            }
        }

        $query->registerRuntimeField(new \Bitrix\Main\Entity\ReferenceField(
            'BE_ELEMENT',
            \Bitrix\Iblock\ElementTable::class,
            \Bitrix\Main\Entity\Query\Join::on('this.biznesUnit', 'ref.ID')
        ));
        $query->where('BE_ELEMENT.IBLOCK_ID', \Immo\Iblock\Manager::getIblockId('be'));

        $query->registerRuntimeField(new \Bitrix\Main\Entity\ReferenceField(
            'ARTICLE_ELEMENT',
            \Bitrix\Iblock\ElementTable::class,
            \Bitrix\Main\Entity\Query\Join::on('this.article', 'ref.ID')
        ));
        $query->where('ARTICLE_ELEMENT.IBLOCK_ID', \Immo\Iblock\Manager::getIblockId('articles'));

        $query
            ->setOrder([
                'BE_ELEMENT.NAME' => 'ASC',
                'ARTICLE_ELEMENT.NAME' => 'ASC',
                'year' => 'DESC',
            ])
            ->setSelect([
                '*',
                'ARTICLE_NAME' => 'ARTICLE_ELEMENT.NAME',
                'BE_NAME' => 'BE_ELEMENT.NAME',
            ]);

        $dbBudget = $query->exec();

        $arData = [];

        $arArticlesIds = [];
        $arBesIds = [];

        $arHashesVsIds = [];

        $maxCumulativeTotal = 0;

        while ($arBudget = $dbBudget->fetch()) {
            $hash = md5($arBudget['biznesUnit'] . $arBudget['article'] . $arBudget['year']);
            $arHashesVsIds[$hash] = [
                'Article' => $arBudget['article'],
                'BEName' => $arBudget['biznesUnit']
            ];
            $arArticlesIds[] = $arBudget['article'];
            $arBesIds[] = $arBudget['biznesUnit'];
            if (!array_key_exists($hash, $arData)) {
                $arData[$hash] = [
                    'year' => $arBudget['year'],
                    'beId' => $arBudget['biznesUnit'],
                    'biznesUnit' => $arBudget['BE_NAME'],
                    'article' => $arBudget['ARTICLE_NAME'],
                    'plan' => 0,
                    'fact' => 0,
                    'inReserve' => 0,
                    'saldo' => 0,
                    'cumulativeTotal' => 0,
                    'total' => 0,
                    'data' => [

                    ]
                ];
                switch ($this->additionalTableParams) {
                    case 'article':
                        $arData[$hash]['articleClass'] = 'clicker';
                        $arData[$hash]['articleData'] = 'data-articleHash=' . $arBudget['unicHash'] . ' data-be=' . $arBudget['biznesUnit'] . ' data-articleId=' . $arBudget['article'];
                }
            }

            if (!array_key_exists($arData[$hash]['data'], $arBudget['month'])) {
                $arData[$hash]['data'][$arBudget['month']] = [
                    'plan' => $arBudget['plan'],
                    'fact' => $arBudget['fact'],
                    'inReserve' => $arBudget['inReserve'],
                    'saldo' => $arBudget['saldo'],
                    'cumulativeTotal' => $arBudget['cumulativeTotal'],
                    'total' => $arBudget['total'],
                ];
                $arData[$hash]['plan'] += $arBudget['plan'];
                $arData[$hash]['fact'] += $arBudget['fact'];
                $arData[$hash]['inReserve'] += $arBudget['inReserve'];
                $arData[$hash]['saldo'] += $arBudget['saldo'];

                if ($arData[$hash]['cumulativeTotal'] <= $arBudget['cumulativeTotal']) {
                    $arData[$hash]['cumulativeTotal'] = $arBudget['cumulativeTotal'];
                }
                $arData[$hash]['total'] += $arBudget['total'];
            }
        }

        $this->arResult['rates'] = [];
        $arBesVsCountries = [];
        $dbBe = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('be')
            ],
            false,
            false,
            [
                'PROPERTY_STRANA',
                'ID'
            ]
        );
        while ($arBe = $dbBe->fetch()) {
            if ($arBe['PROPERTY_STRANA_VALUE'] > 0) {
                $arBesVsCountries[$arBe['ID']] = $arBe['PROPERTY_STRANA_VALUE'];
            }
        }
        if(empty($arBesVsCountries)){
            throw new Exception('empty - 270 .all');
        }
        $countriesDb = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('country'),
                'ID' => $arBesVsCountries
            ],
            false,
            false,
            [
                'PROPERTY_VALYUTA',
                'ID'
            ]
        );

        $arCountryVsRate = [];

        while ($arCountry = $countriesDb->fetch()) {
            if ($arCountry['PROPERTY_VALYUTA_VALUE'] > 0) {
                $arCountryVsRate[$arCountry['ID']] = $arCountry['PROPERTY_VALYUTA_VALUE'];
            }
        }

        $dbRates = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('currencies_ib')
            ],
            false,
            false,
            [
                'ID',
                'IBLOCK_ID',
                'PROPERTY_KOD_VALYUTY'
            ]
        );

        $arRates = [];

        while ($arRate = $dbRates->fetch()) {
            $arRates[$arRate['ID']] = $arRate['PROPERTY_KOD_VALYUTY_VALUE'];
        }

        foreach ($arBesVsCountries as $be => $country) {
            $rateId = $arCountryVsRate[$country];
            $this->arResult['rates'][$be] = $arRates[$rateId];
        }

        if (!empty($arData) and !empty($this->arResult['rates'])) {
            foreach ($arData as $index => $data) {
                if (!array_key_exists($data['beId'], $this->arResult['rates'])) {
                    continue;
                }

                $arData[$index]['biznesUnit'] .= " ({$this->arResult['rates'][$data['beId']]})";
            }
        }

        $arBesNames = [];

        if(!empty($arBesIds)){
            $dbBes = CIBlockElement::getList(
                [],
                [
                    'ID' => $arBesIds
                ],
                false,
                false,
                [
                    'NAME',
                    'ID'
                ]
            );

            while ($arBe = $dbBes->fetch()) {
                $arBesNames[$arBe['ID']] = $arBe['NAME'];
            }

            $this->arResult['data'] = $arData;
        }


    }

}
