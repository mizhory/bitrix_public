<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}



class CBRoles extends CBitrixComponent
{
    public function executeComponent()
    {
        switch ($this->arParams['page']) {
            case 'edit':
                $this->buildDataByEdit();
                break;
            case 'view':
                $this->buildDataByView();
                break;
        }
        $this->IncludeComponentTemplate();
    }

    public function buildDataByEdit()
    {
        $iblockId = getIblockIdByCode('departments');

        $arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$iblockId.'_SECTION',0,'ru');

        $arFields = [];

        foreach ($arUserFields as $key=>$arUserField){
            if(preg_match('/UF_ROLE_/',$key)){
                $arFields[$key] = $arUserField['EDIT_FORM_LABEL'];
            }
        }

        $this->arResult['DATA'] = $arFields;
        $arValues = $this->arParams['userField'];
    }

    public function buildDataByView()
    {
        $iblockId = getIblockIdByCode('departments');

        $arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$iblockId.'_SECTION',0,'ru');

        $arFields = [];

        foreach ($arUserFields as $key=>$arUserField){
            if($key == $this->arParams['value']['VALUE']){
                $this->arResult['name'] = $arUserField['EDIT_FORM_LABEL'];
            }
        }

    }

}