<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Class CBudgetList
 * Основная страницы бюджета(с выборами)
 */
class CBudgetList extends CBitrixComponent
{
    public function executeComponent()
    {
        $this->buildData();
        $this->IncludeComponentTemplate();
    }

    /**
     * @throws Exception
     * построение даты
     */
    public function buildData()
    {
        $arElements = [];
        $ib = getIblockIdByCode('be');
        if($ib > 0){
            $dbElements = \CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID'=>getIblockIdByCode('be')
                ]
            );
            while($arElement = $dbElements->fetch()){
                $arElements[] = $arElement;
            }

        }

        $this->arResult['ELEMENTS'] = $arElements;
    }
}
