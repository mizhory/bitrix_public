<?php

namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Immo\Iblock\Manager;
use Immo\Iblock\Property\BiznesUnitsIblockField;
use Immo\Integration\Budget\BudgetHelper;
use Immo\Integration\Budget\CurrencyManager;
use Immo\Structure\Organization;

\CBitrixComponent::includeComponentClass('vigr:budget.be');

/**
 * @description Компонент для вывода формы БЕ в форме УС
 * Class IblockBudgetBiznesUnits
 * @package Immo\Components
 */
class IblockBudgetBiznesUnits extends \CBudgetList
{
    public function configureActions()
    {
        $actions = parent::configureActions();
        $actions['getBudgets'] = $actions['getRates'];
        $actions['getCurrencyRate'] = $actions['getRate'];
        return $actions;
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->prepareExtraData();

        if ($this->arParams['MULTIPLE'] == 'Y') {
            $this->prepareMultiValues();
        } else {
            $this->prepareSingleValue();
        }

        $this->IncludeComponentTemplate();

        return $this->arResult;
    }

    /**
     * @description Подготовка множественных значений. Используется в АО
     * @throws \Exception
     */
    protected function prepareMultiValues(): void
    {
        $this->unsetBeByParent();

        if (empty($this->arParams['userField']['VALUE'])) {
            return;
        }

        $this->arResult['VALUES'] = BiznesUnitsIblockField::decodeValue($this->arParams['userField']['VALUE']);
        $arBeIds = [];
        foreach ($this->arResult['VALUES'] as $arBe) {
            $arBeIds = array_merge($arBeIds, array_keys($arBe['items']));
        }

        if (!empty($this->arResult['VALUES']['items'])) {
            $this->arResult['ALL_BE_PRODUCTS'] = $this->getAllBeProducts([
                'IBLOCK_ID' => getIblockIdByCode('be_products'),
                'PROPERTY_BE_LIST' => array_keys($this->arResult['VALUES']['items'])
            ]);
        } elseif (!empty($arBeIds)) {
            $this->arResult['ALL_BE_PRODUCTS'] = $this->getAllBeProducts([
                'IBLOCK_ID' => getIblockIdByCode('be_products'),
                'PROPERTY_BE_LIST' => $arBeIds
            ]);
        }

        foreach ($this->arResult['VALUES'] as $arBe) {
            $arBe['be'] = array_keys($arBe['items']);
        }

        $this->arResult['AR_BUDGETS'] = [];
    }

    /**
     * @description Удаляет лишние БЕ, если заявка создана автоматически по финансовой заявке
     */
    protected function unsetBeByParent(): void
    {
        if (empty($this->arParams['PARENT']['BE']['items']) or $this->arParams['PARENT']['AUTO_CREATE'] == 'N') {
            return;
        }

        foreach ($this->arResult['ALL_BE'] as $id => $name) {
            if (in_array($id, array_keys($this->arParams['PARENT']['BE']['items']))) {
                continue;
            }

            unset($this->arResult['ALL_BE'][$id]);
        }
    }

    /**
     * @description Подготовка значений для формы
     * @throws \Exception
     */
    protected function prepareSingleValue(): void
    {
        if (empty($this->arParams['userField']['VALUE'])) {
            return;
        }

        $arValues = BiznesUnitsIblockField::decodeValue($this->arParams['userField']['VALUE']);

        $arBeS = [
            'be' => [],
            'article' => $arValues['article'],
            'month' => $arValues['month'],
            'year' => $arValues['year'],
            'beRateValue' => $arValues['rateBe'],
            'baseRate' => $arValues['rate'],
            'rateValue' => $arValues['rateCurse']
        ];

        foreach ($arValues['items'] as $arItem){
            $arBeS['be'][] = $arItem['id'];
        }

        $this->arResult['AR_BUDGETS'] = $this->getRatesAction($arBeS);
        $this->arResult['MAIN_SUM'] = $arValues['sum'];
        $this->arResult['BANK_BE'] = $arValues['beBank'];
        $this->arResult['DISTR'] = $arValues['distr'];
        $this->arResult['COUNTRY_NOW'] = $arValues['country'];
        $this->arResult['DRAFT'] = $arValues['draft'];
        $this->arResult['UR'] = $arValues['ur'];
        $this->arResult['RATE_CURSE'] = $arValues['rateCurse'];
        $this->arResult['RATE_CURRENCY_RATE'] = $arValues['dateCurrencyRate'];
        $this->arResult['RATE'] = $arValues['rate'];
        $this->arResult['RATE_BE'] = $arValues['rateBe'];
        $this->arResult['MONTH'] = $arValues['month'];
        $this->arResult['YEAR'] = $arValues['year'];
        $this->arResult['ARTICLE'] = $arValues['article'];
        $this->arResult['FREE_SUM'] = $arValues['freeSum'];
        $this->arResult['FREE_PERCENT'] = $arValues['freePercent'];
        $this->arResult['ITEMS'] = $arValues['items'];
        $this->arResult['ALL_BE_PRODUCTS'] = $this->getAllBeProducts([
            'IBLOCK_ID' => getIblockIdByCode('be_products'),
            'PROPERTY_BE_LIST' => array_keys($arValues['items'])
        ]);
    }

    /**
     * @description Подготовка доп данных для работы формы
     */
    protected function prepareExtraData(): void
    {
        $arAllBe =  $this->getAllBe();

        //$this->arResult['RATES'] = $this->getAllRates();
        $this->arResult['COUNTRY'] = $this->getCountry();
        $this->arResult['ARTICLES'] = $this->getAllArticles();

        $this->arResult['UR_LISTS'] = $this->getUrList();

        $this->arResult['UR_LISTS_VS_BE'] = $arAllBe['Ur'];
        $this->arResult['BE_VS_COUNTRY'] = $arAllBe['BesVsCountry'];

        $this->arResult['ALL_BE'] = $arAllBe['Bes'];
        $this->arResult['CURRENCY_LIST'] = $this->getCurrency();

        $this->arResult['BANK'] = ($this->arParams['USE_BANK'])
            ? $this->getBankParams(
                $this->arParams['BANK_FIELD'],
                $this->arParams['IBLOCK_LIST']['BIZNES_UNITS']
            )
            : [];
    }

    /**
     * @description Подготавливает параметры для поле внутреннго банка
     * @param string $bankField
     * @param string $iblockCode
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getBankParams(string $bankField, string $iblockCode): array
    {
        static $result = [];
        if (!empty($result)) {
            return $result;
        }

        $beIblockId = Manager::getIblockId($iblockCode);

        if (!empty($bankField) and $beIblockId > 0) {
            foreach ($this->arResult['COUNTRY'] as $country) {
                if ($country['DEFAULT'] != 'Y') {
                    continue;
                }

                $defCountry = $country;
                break;
            }

            $rsBanks = \CIBlockElement::GetList([], [
                'IBLOCK_ID' => $beIblockId,
                "!PROPERTY_{$bankField}" => false
            ], false, false, [
                'ID',
                'IBLOCK_ID',
                'ACTIVE',
                'PROPERTY_STRANA',
                "PROPERTY_{$bankField}"
            ]);

            while ($bank = $rsBanks->Fetch()) {
                if (isset($defCountry)) {
                    if (empty($bank['PROPERTY_STRANA_VALUE']) or $bank['PROPERTY_STRANA_VALUE'] != $defCountry['ID']) {
                        continue;
                    } else {
                        $arBanksElement[$bank['ID']] = [
                            'NAME' => $bank["PROPERTY_{$bankField}_VALUE"],
                            'ACTIVE' => $bank['ACTIVE']
                        ];
                    }
                } else {
                    $arBanksElement[$bank['ID']] = [
                        'NAME' => $bank["PROPERTY_{$bankField}_VALUE"],
                        'ACTIVE' => $bank['ACTIVE']
                    ];
                }
            }
        }

        $result = [
            'elementBank' => $arBanksElement ?? [],
        ];

        return $result;
    }

    /**
     * @description Возвращает html продуктов и сумму бюджета
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getProductsAction()
    {
        \CModule::includeModule('vigr.budget');
        $context = \Bitrix\Main\Application::getInstance()->getContext();

        $arValues = $context->getRequest()->getValues();

        $budget = \Vigr\Budget\Internals\BudgetTable::getList(
            [
                'filter' => [
                    'unicHash' => md5($arValues['be'] . $arValues['article'] . $arValues['month'] . $arValues['year'])
                ]
            ]
        )->fetch()['total'];

        //$budget = round($budget / $arValues['rateCurse'],2);

        $ibLockId = getIblockIdByCode('be_products');

        $arFilter = [
            'IBLOCK_ID' => $ibLockId,
            'PROPERTY_BE_LIST' => $arValues['be']
        ];

        $arProductsAll = $this->getAllBeProducts($arFilter);
        ob_start();
        $arProducts = $arProductsAll[$arValues['be']];
        include 'templates/' . ($arValues['template'] ?? BiznesUnitsIblockField::TEMPLATE_VERSION) . '/beProduct.php';

        $html = ob_get_clean();

        return [
            'html'=>$html,
            'budget'=> empty($budget) ? 0 : $budget
        ];
    }

    /**
     * @description Возвращает остаток по бюджету по статье, месяцу, году и БЕ
     * @param int $article
     * @param int $month
     * @param int $year
     * @param array $be
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getBudgetsAction(int $article, int $month, int $year, array $be): array
    {
        if (empty($be)) {
            return [];
        }

        $arHashes = [];
        foreach ($be as $id){
            $arHashes[$id] = md5("{$id}{$article}{$month}{$year}");
        }

        $dbBudgets = \Vigr\Budget\Internals\BudgetTable::query()
            ->whereIn('unicHash', array_values($arHashes))
            ->setSelect(['biznesUnit', 'cumulativeTotal'])
            ->exec();

        $arBudgets = [];
        while ($arBudget = $dbBudgets->fetch()) {
            $arBudgets[$arBudget['biznesUnit']] = $arBudget['cumulativeTotal'];
        }

        foreach ($arHashes as $beId => $hash) {
            if (empty($arBudgets[$beId])) {
                $arBudgets[$beId] = 0;
            }
        }

        return $arBudgets;
    }

    /**
     * @description Возвращает статьи
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllArticles()
    {
        return BudgetHelper::getActiveArticles();
    }

    /**
     * @description Возвращает страны
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCountry(){
        static $arCountryAll = [];
        if (!empty($arCountryAll)) {
            return $arCountryAll;
        }

        $dbCountry = \CIBlockElement::getList(
            [
                'NAME'=>"ASC"
            ],
            [
                'IBLOCK_ID' => Manager::getIblockId($this->arParams['IBLOCK_LIST']['COUNTRY']) ?? 0
            ],
            false,
            false,
            [
                'ID',
                'ACTIVE',
                'PROPERTY_VALYUTA',
                'PROPERTY_DEFAULT',
                'NAME'
            ]
        );

        $defaultProperty = Manager::getPropertyByCode(
            'DEFAULT',
            (int)Manager::getIblockId($this->arParams['IBLOCK_LIST']['COUNTRY'])
        );
        if (!empty($defaultProperty)) {
            foreach ($defaultProperty['VALUES'] as $enum) {
                if ($enum['XML_ID'] != 'Y') {
                    continue;
                }

                $defaultEnum = $enum;
                break;
            }
        }

        while($arCountry = $dbCountry->Fetch()){
            $arCountryAll[$arCountry['ID']] = [
                'ID'=>$arCountry['ID'],
                'RATE_ID'=>$arCountry['PROPERTY_VALYUTA_VALUE'],
                'NAME'=>$arCountry['NAME'],
                'ACTIVE' => $arCountry['ACTIVE'],
                'DEFAULT' => (!empty($defaultEnum) and $arCountry['PROPERTY_DEFAULT_ENUM_ID'] == $defaultEnum['ID'])
                    ? 'Y'
                    : 'N'
            ];
        }

        return $arCountryAll;
    }

    /**
     * @description Возвращает юр лица
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getUrList(){
        static $arCountryAll = [];
        if (!empty($arCountryAll)) {
            return $arCountryAll;
        }

        $dbCountry = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => Manager::getIblockId($this->arParams['IBLOCK_LIST']['LEGAL']) ?? 0
            ],
            false,
            false,
            [
                'ID',
                'ACTIVE',
                'NAME'
            ]
        );

        while($arCountry = $dbCountry->fetch()){
            $arCountryAll[$arCountry['ID']] = [
                'NAME' => $arCountry['NAME'],
                'ACTIVE' => $arCountry['ACTIVE']
            ];
        }

        return $arCountryAll;
    }

    /**
     * @description Возвращает список привязок оргструктуры
     * @return array[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllBe()
    {
        static $result = [];
        if (!empty($result)) {
            return $result;
        }

        $arStructure = Organization::getStructure();
        if (empty($arStructure)) {
            return [];
        }

        foreach ($arStructure as $id => ['BE' => $arBe, 'COMPANIES' => $companies]) {
            if (empty($arBe['OLD_ID'])) {
                continue;
            }

            $arBesVsCountry[$arBe['OLD_ID']] = $arBe['UF_COUNTRY'];
            $arBes[$arBe['OLD_ID']] = [
                'ACTIVE' => $arBe['ACTIVE'],
                'NAME' => $arBe['NAME']
            ];
            if (empty($companies)) {
                continue;
            }

            foreach ($companies as $company) {
                $arUrsList[$company['OLD_ID']] = $arBe['OLD_ID'];
            }
        }

        $result = [
            'Bes' => $arBes ?? [],
            'Ur' => $arUrsList ?? [],
            'BesVsCountry' => $arBesVsCountry ?? []
        ];

        return $result;
    }

    /**
     * @description Возвращает список валют
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCurrency()
    {
        static $arRates = [];
        if (!empty($arRates)) {
            return $arRates;
        }

        $dbRates = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => Manager::getIblockId($this->arParams['IBLOCK_LIST']['CURRENCY']) ?? 0,
            ],
            false,
            false,
            [
                'PROPERTY_KOD_VALYUTY',
                'NAME',
                'ACTIVE',
                'ID'
            ]
        );

        while ($arRate = $dbRates->fetch()) {
            $arRates[$arRate['ID']] = [
                'NAME'=>$arRate['NAME'],
                'ID'=>$arRate['ID'],
                'CODE'=>$arRate['PROPERTY_KOD_VALYUTY_VALUE'],
                'ACTIVE' => $arRate['ACTIVE']
            ];
        }

        return $arRates;
    }

    /**
     * @description Возвращает курс валюты
     * @param string $currency
     * @param string $currencyPayed
     * @return string
     * @throws \Bitrix\Main\SystemException
     */
    public function getCurrencyRateAction(string $currency, string $currencyPayed): array
    {
        $currencyManager = new CurrencyManager();
        return $currencyManager->getCurrencyRub($currencyPayed);
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
            'ACTIVE',
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
            $arProducts[$arProduct['PROPERTY_BE_LIST_VALUE']][$arProduct['ID']] = [
                'NAME' => $arProduct['NAME'],
                'ACTIVE' => $arProduct['ACTIVE']
            ];
        }

        return $arProducts;
    }
}