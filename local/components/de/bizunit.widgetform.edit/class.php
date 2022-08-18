<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
class BizunitWidgetformEdit extends CBitrixComponent {

    const IBLOCK_ID = 3;

    const BIZ_UNIT_BUDGET_IBLOCK_ID = [
        275, // 9.1
    ];


    public function executeComponent() {
        //echo "<pre>"; print_r($this->arParams); echo "</pre>";
        $this->arResult['DATA_BIZ_UNIT'] = $this->getDataBizUnit();
        $this->arResult['CURRENCY_LIST'] = $this->getСurrencyList();

        /*
        $this->arResult['DATA_BIZ_UNIT'] = $this->getDataBizUnit();
        $this->arResult['ALL_SUM'] = $this->getAllSum();

        $this->arResult['ALL_SUM_CURRENCY_ID'] = $this->getAllSumCurrency();


        $this->arResult['ALL_SUM_FORMAT'] = $this->numberFormat($this->getAllSum());
        $this->arResult['ALL_PSNT'] = $this->getAllPsnt();
        $this->arResult['ALL_PSNT_FORMAT'] = $this->numberFormat($this->getAllPsnt());
        $this->arResult['AR_BIZ_UNIT'] = $this->getSelectItems();

        $this->arResult['AR_BIZ_UNIT_BUDGET'] = $this->getBizUnitBudget();

        $this->arResult['CURRENCY_LIST'] = $this->getСurrencyList();

        */

        $this->includeComponentTemplate();
    }

    protected function numberFormat($number){
        $number = round($number, 2);
        return number_format($number, 2, ".", "");
    }

    protected function getBizUnitBudget(){
        $year = 2020;
        $arBizUnitBudget = [];
        foreach(self::BIZ_UNIT_BUDGET_IBLOCK_ID as $bizUnitBudgetIblockId){
            $budget = new \De\BizUnitBudget($bizUnitBudgetIblockId, $year);
            $arData = $budget->getDeal();

            foreach ($arData as $key => $value) {
                $arBizUnitBudget[$bizUnitBudgetIblockId][$key]['TOTAL'] = $value['TOTAL'];
                $arBizUnitBudget[$bizUnitBudgetIblockId][$key]['THIS_MONTH'] = $value['BALANCE'] - $value['IN_DEAL'];
            }
            //print_de($arBizUnitBudget);
        }
        return $arBizUnitBudget;
    }


    protected function getProductByBizUnit($ufDepartment){
        $departmentProduct = [];
        $allProduct = $this->getAllProduct();
        if(isset($allProduct[$ufDepartment])){
            $departmentProduct = $allProduct[$ufDepartment];
        }
        return $departmentProduct;
    }

    protected function getСurrencyList(){
        $arCurrency = [
            "RUB" => 'Российский рубль',
            "USD" => 'Доллар США',
            "EUR" => 'Евро',
            "BYN" => 'Белоруский рубль',
            "UAH" => 'Украинская гривна',
            "KZT" => 'Казахстанский тенге',
            "GBP" => 'Фунт стерлингов',
        ];
        return $arCurrency;
    }


    protected function getAllPsnt(){
        return $this->arParams['ALL_PSNT'];
    }


    protected function getAllSum(){
        return $this->arParams['ALL_SUM'];
    }

    protected function getAllSumCurrency(){
        return $this->arParams['ALL_SUM_CURRENCY_ID'];
    }


    protected function getDataBizUnit(){
        return $this->arParams['BIZ_UNITS_DATA'];
    }

    private function getSelectItems(){

        $arFilter = ['IBLOCK_ID' => 17, 'GLOBAL_ACTIVE' => 'Y'];
        $arSelect = ['ID', 'NAME', 'PROPERTY_ID_BE'];

        $arElemItem = [];

        $rsElem = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

        while($elem = $rsElem->Fetch()){
            $arElemItem[$elem['PROPERTY_ID_BE_VALUE']] = $elem['ID'];
        }


        $rsSection = \Bitrix\Iblock\SectionTable::getList([
            'select' => ['ID', 'NAME'],
            'filter' => [
                'IBLOCK_ID' => self::IBLOCK_ID, 'GLOBAL_ACTIVE' => 'Y', 'IBLOCK_SECTION_ID' => [1, 23]
            ],
            'order' => ['NAME' => 'ASC']
        ]);

        $arSelectItem = [];
        while($arSection = $rsSection->Fetch()){
            if(isset($arElemItem[$arSection['ID']])){
                $arSelectItem[$arElemItem[$arSection['ID']]] = $arSection['NAME'];
            }
        }
        return $arSelectItem;
    }

}