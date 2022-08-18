<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Class CBudgetDaData
 * Дадата ИНН и наименование в сделке
 */
class CBudgetDaData extends CBitrixComponent
{
    public function executeComponent()
    {
        if($this->arParams['userField']['VALUE']){
            $this->arResult['arValues'] = json_decode(html_entity_decode($this->arParams['userField']['VALUE']),1);
        }

        $this->IncludeComponentTemplate();
    }
}