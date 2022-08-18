<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

/**
 * Class CBudgetList
 * компонент отображения БЕ
 */
class CBudgetList extends CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        $this->arResult = $this->getData($this->arParams['userField']['VALUE']);

        global $USER;

        $arGroups = $USER->GetUserGroupArray();

        $this->arResult['FD'] = 'N';

        if(in_array(17,$arGroups) || $USER->isAdmin()){
            $this->arResult['FD'] = 'Y';
        }

        if($this->arParams['userField']['ENTITY_VALUE_ID']){
            $arData = CCrmDeal::GetByID($this->arParams['userField']['ENTITY_VALUE_ID']);
            $stageId = $arData['STAGE_ID'];

            if($arData['TYPE_ID'] === 'SALE'){
                $arCan = [
                    'NEW',
                    '11',
                    'PREPARATION'
                ];

                if(!in_array($stageId,$arCan)){
                    $this->setTemplateName('view');
                }
            }


        }

        $this->IncludeComponentTemplate();
    }

    /**
     * @param $arFieldValue
     * @return array
     * Получить данные
     */
    public function getData($arFieldValue){
        $arData = [];
        $arData['CURRENCY_LIST'] = $this->getCurrency();

        $arAllBe =  $this->getAllBe();

        $arData['COUNTRY'] = $this->getCountry();
        $arData['ARTICLES'] = $this->getAllArticles();

        $arData['UR_LISTS'] = $this->getUrList();

        $arData['UR_LISTS_VS_BE'] = $arAllBe['Ur'];
        $arData['BE_VS_COUNTRY'] = $arAllBe['BesVsCountry'];

        $arData['ALL_BE'] =$arAllBe['Bes'];

        if ($arFieldValue && $arFieldValue !== '') {
            $arValues = returnValideJson($arFieldValue);

            $arBeS = [
                'be'=>[

                ],
                'article'=>$arValues['article'],
                'month'=>$arValues['month'],
                'year'=>$arValues['year'],
                'beRateValue'=>$arValues['rateBe'],
                'baseRate'=>$arValues['rate'],
                'rateValue'=>$arValues['rateCurse']
            ];

            foreach ($arValues['items'] as $arItem){
                $arBeS['be'][] = $arItem['id'];
            }

            $arData['AR_BUDGETS'] = $this->getRatesAction($arBeS);
            $arData['MAIN_SUM'] = $arValues['sum'];
            $arData['DISTR'] = $arValues['distr'];
            $arData['COUNTRY_NOW'] = $arValues['country'];
            $arData['DRAFT'] = $arValues['draft'];
            $arData['UR'] = $arValues['ur'];
            $arData['RATE_CURSE'] = $arValues['rateCurse'];
            $arData['RATE'] = $arValues['rate'];
            $arData['RATE_BE'] = $arValues['rateBe'];
            $arData['MONTH'] = $arValues['month'];
            $arData['YEAR'] = $arValues['year'];
            $arData['ARTICLE'] = $arValues['article'];
            $arData['FREE_SUM'] = $arValues['freeSum'];
            $arData['FREE_PERCENT'] = $arValues['freePercent'];
            $arData['ITEMS'] = $arValues['items'];

            $arData['ALL_BE_PRODUCTS'] = $this->getAllBeProducts(['IBLOCK_ID' => 29,'PROPERTY_BE_LIST'=>array_keys($arValues['items'])]);
        }

        return $arData;
    }

    /**
     * @return array
     * @throws Exception
     * получает все статьи
     */
    public function getAllArticles(){
        $dbArticles = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('articles')
            ],
            false,
            false,
            [
                'ID',
                'NAME'
            ]
        );

        $arArticles = [];

        while($arArticle = $dbArticles->fetch()){
            $arArticles[$arArticle['ID']] = $arArticle['NAME'];
        }

        return $arArticles;
    }

    /**
     * @return array
     * @throws Exception
     * получить страну
     */
    public function getCountry(){
        $dbCountry = \CIBlockElement::getList(
            [
                'NAME'=>"ASC"
            ],
            [
                'IBLOCK_ID' => getIblockIdByCode('country')
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_VALYUTA',
                'NAME'
            ]
        );

        $arCountryAll = [];

        while($arCountry = $dbCountry->fetch()){
            $arCountryAll[$arCountry['ID']] = [
                'ID'=>$arCountry['ID'],
                'RATE_ID'=>$arCountry['PROPERTY_VALYUTA_VALUE'],
                'NAME'=>$arCountry['NAME']
            ];
        }

        return $arCountryAll;
    }

    /**
     * @return array
     * @throws Exception
     * получить спиок юр.лиц
     */
    public function getUrList(){
        $dbCountry = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('companies')
            ],
            false,
            false,
            [
                'ID',
                'NAME'
            ]
        );

        $arCountryAll = [];

        while($arCountry = $dbCountry->fetch()){
            $arCountryAll[$arCountry['ID']] = $arCountry['NAME'];
        }

        return $arCountryAll;
    }

    public function configureActions()
    {
        return [
            'getProducts' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'getRates' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'getRate'=>[
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    /**
     * @return string
     * @throws Exception
     * получить курс
     */
    public function getRateAction(){
        CModule::includeModule('vigr.budget');
        $context = Bitrix\Main\Application::getInstance()->getContext();

        $arValues = $context->getRequest()->getValues();

        $budget = new Vigr\Budget\Budget();
        return $budget->getRateByRub($arValues['currency'],$arValues['currencyPayed']);
    }

    /**
     * @return array
     * @throws Exception
     * получить продукты
     */
    public function getProductsAction()
    {
        CModule::includeModule('vigr.budget');
        $context = Bitrix\Main\Application::getInstance()->getContext();

        $arValues = $context->getRequest()->getValues();

        $budget = \Vigr\Budget\Internals\BudgetTable::getList(
            [
                'filter' => [
                    'unicHash' => md5($arValues['be'] . $arValues['article'] . returnNameMonth($arValues['month']) . $arValues['year'])
                ]
            ]
        )->fetch()['cumulativeTotal'];

        $ibLockId = getIblockIdByCode('be_products');

        $arFilter = [
            'IBLOCK_ID' => $ibLockId,
            'PROPERTY_BE_LIST' => $arValues['be']
        ];

        $arProductsAll = $this->getAllBeProducts($arFilter);

        ob_start();
        $arProducts = $arProductsAll[$arValues['be']];
        include 'templates/.default/beProduct.php';

        $html = ob_get_clean();

        return [
            'html'=>$html,
            'budget'=>$budget
        ];
    }


    /**
     * @param array $arValues
     * @return array
     * получить бюджет для БЕ
     */
    public function getRatesAction($arValues = []){
        $context = Bitrix\Main\Application::getInstance()->getContext();

        $arHashes = [];

        if(empty($arValues)){
            $arValues = $context->getRequest()->getValues();
        }

        foreach ($arValues['be'] as $value){
            $month = returnNameMonth($arValues['month']);
            if (empty($month)) {
                $month = $arValues['month'];
            }
            $arHashes[] = md5($value . $arValues['article'] . $month . $arValues['year']);
        }

        $dbBudgets = \Vigr\Budget\Internals\BudgetTable::getList(
            [
                'filter' => [
                    'unicHash' => $arHashes
                ]
            ]
        );

        $arBudgets = [];

        $budget = new Vigr\Budget\Budget();

        while($arBudget = $dbBudgets->fetch()){
            $arBudgets[$arBudget['biznesUnit']] = $arBudget['cumulativeTotal'];
        }

        return $arBudgets;
    }

    /**
     * @param $arFilter
     * @return array
     * получить все продукты
     */
    public function getAllBeProducts($arFilter)
    {
        $arSelect = [
            'NAME',
            'ID',
            'IBLOCK_ID',
            "PROPERTY_NAZVANIE_BE",
            "PROPERTY_BE_LIST",
        ];

        $dbProducts = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

        $arProducts = [];

        while ($arProduct = $dbProducts->fetch()) {
            if (!array_key_exists($arProduct['PROPERTY_BE_LIST_VALUE'], $arProducts)) {
                $arProducts[$arProduct['PROPERTY_BE_LIST_VALUE']] = [];
            }
            $arProducts[$arProduct['PROPERTY_BE_LIST_VALUE']][$arProduct['ID']] = $arProduct['NAME'];
        }

        return $arProducts;
    }

    /**
     * @return array[]
     * получить все БЕ
     */
    public function getAllBe()
    {
        $arBes = [];
        $dbBe = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('be')
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_STRANA',
                'NAME',
                'PROPERTY_YUR_LITSA'
            ]
        );

        $arUrsList = [];
        $arBesVsCountry = [];

        while ($arBe = $dbBe->fetch()) {
            if($arBe['PROPERTY_STRANA_VALUE']){
                $arUrsList[$arBe['PROPERTY_YUR_LITSA_VALUE']] = $arBe['ID'];
            }

            if($arBe['PROPERTY_STRANA_VALUE']){
                $arBesVsCountry[$arBe['ID']] = $arBe['PROPERTY_STRANA_VALUE'];
            }

            $arBes[$arBe['ID']] = $arBe['NAME'];
        }

        return [
            'Bes'=>$arBes,
            'Ur'=>$arUrsList,
            'BesVsCountry'=>$arBesVsCountry
        ];
    }

    /**
     * @return array
     */
    public function getCurrency()
    {
        $dbRates = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('currencies_ib'),
            ],
            false,
            false,
            [
                'PROPERTY_KOD_VALYUTY',
                'NAME',
                'ID'
            ]
        );

        $arRates = [];

        while ($arRate = $dbRates->fetch()) {
            $arRates[$arRate['ID']] = [
                'NAME'=>$arRate['NAME'],
                'ID'=>$arRate['ID'],
                'CODE'=>$arRate['PROPERTY_KOD_VALYUTY_VALUE']
            ];
        }

        return $arRates;
    }


}