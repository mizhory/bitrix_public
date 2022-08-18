<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
class BizunitProductsWidgetformEdit extends CBitrixComponent {
    const IBLOCK_ID = 3;


    public function executeComponent() {
        //$this->arResult['AR_BIZ_UNIT_PRODUCTS'] = $this->getDataBizUnitProducts();
        //$this->arResult['ALL_SUM'] = $this->arParams['ALL_SUM'];
        //$this->arResult['ALL_PSNT'] = 100;

        //$bizUnitId = $this->arParams['BIZ_UNIT_ID'];
        //$bizUnitKey = $this->arParams['BIZ_UNIT_KEY'];



        $this->arResult = [
            'DATA_BIZ_UNIT_PRODUCTS'=>$this->arParams['DATA_BIZ_UNIT_PRODUCTS'],
            'ALL_PRODUCTS'=>$this->arParams['ALL_PRODUCTS'],
            'BI_ID'=>$this->arParams['BIZ_UNIT_ID']
        ];

        if($_REQUEST['ajax']){
            $this->arResult = [
                'ALL_PRODUCTS'=>$this->getAllProduct($_REQUEST['BIZ_UNIT_ID']),
                'DATA_BIZ_UNIT_PRODUCTS'=>[
                    [
                        'SUM'=>0,
                        'PSNT'=>0,
                    ]
                ],
                'BI_ID'=>$_REQUEST['BIZ_UNIT_ID']
            ];
        }

        if(!empty($this->arResult['ALL_PRODUCTS'])){
            $this->includeComponentTemplate();
        }
    }


    protected function getSelectItems($ufDepartment){
        $departmentProduct = [];
        $allProduct = $this->getAllProduct();
        if(isset($allProduct[$ufDepartment])){
            $departmentProduct = $allProduct[$ufDepartment];
        }
        return $departmentProduct;
    }

    protected function getAllProduct($id){

        $ibLockId = 29;

        $arFilter = [
            'IBLOCK_ID' => $ibLockId,
            'PROPERTY_BE_LIST'=>$id
        ];
        $arSelect = [
            'NAME',
            'ID',
            "PROPERTY_NAZVANIE_BE",
            "PROPERTY_BE_LIST",
        ];

        $db = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

        while($resDb = $db->Fetch()){
            $biId = $resDb['PROPERTY_BE_LIST_VALUE'];
            $id = $resDb['ID'];
            $arProduct[$biId][$id] = [
                'NAME'=>$resDb['NAME'],
                'ID'=>$resDb['ID']
            ];
        }

        return $arProduct;
    }


    protected function getAllPsnt(){
        return $this->arParams['ALL_PSNT'];
    }


    protected function getAllSum(){
       return $this->arParams['ALL_SUM'];
    }

    protected function getDataBizUnitProducts(){
        return $this->arParams['DATA_BIZ_UNIT_PRODUCTS'];
    }
}