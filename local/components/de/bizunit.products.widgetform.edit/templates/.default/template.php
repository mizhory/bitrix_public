<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Application;

\Bitrix\Main\UI\Extension::load("ui.forms");

$prefix = $arParams['PREFIX_ID'];

if($arParams['AJAX_MODE'] == 'Y'){
    ;
}
?>

<div style="width: 100%" class="de-inline-bl">
          <span class="field-wrap distribution fields boolean" style = 'display: {displaySwitch}'>
            <span class="field-item fields boolean">
                <input class="fields boolean" type="hidden" value="0" name="<?=$prefix?>_distribution_0">
                    <label style="width: 230px">
                        <input type="checkbox" class = 'distribution' value="1" name="<?=$prefix?>_distribution_0">
                        Распределить по всем в равных %
                    </label>
            </span>
        </span>
    <span style = 'display: {displaySwitch}' class="field-wrap distribution fields boolean">
            <span class="field-item fields boolean">
                <input  class="fields boolean" type="hidden" value="0" name="<?=$prefix?>_distribution_0">
                    <label>
                        <input type="radio" checked  class = 'switchProduct_0' value="noP" name="<?=$prefix?>_switcher_0">
                        Без продуктов
                    </label>
                    <label style="width: 163px">
                        <input type="radio"  class = 'switchProduct_0' value="userP" name="<?=$prefix?>_switcher_0">
                        Пользовательский набор
                    </label>
                    <label>
                        <input type="radio" class = 'switchProduct_0' value="allP" name="<?=$prefix?>_switcher_0">
                        Все продукты
                    </label>
            </span>
        </span>
</div>

<?php
ob_start();

?>
<div class="beproduct-item be-item-hide" data-num = "{num}" data-value="{value}" data-id = "{num}" id="<?=$prefix?>_wrap_item_{num}">

    <div class="de-inline-bl de-bi " >
        <div class="ui-entity-editor-block-title de-block de-label">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">
            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <select disabled value="{bi}" data-num="{num}" id="<?=$prefix?>_selbi_{num}" class="ui-ctl-element select-productbizunit" >
                <option value = '0' class="<?=$prefix?>-options">Не выбрано</option>
                <!--
                <option value = '99999992' class="<?=$prefix?>-options">Не выбрано</option>
                <option value = '99999991' class="<?=$prefix?>-options">Без продуктов</option>
                <option value = '99999999' class="<?=$prefix?>-options">Все продукты</option>
                -->
                {selects}
            </select>
        </div>
    </div>

    <div class="de-inline-bl de-psnt">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input data-num="{num}" id="<?=$prefix?>_psnt_{num}" disabled type="text" class="percentInput ui-ctl-element" value="{psnt}">
        </div>

    </div>

    <div class="de-inline-bl de-psnt" style="display: none">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input data-num="{num}" id="<?=$prefix?>_budget_{num}" disabled type="text" class="budgetInput ui-ctl-element" value="{psnt}">
        </div>

    </div>

    <div class="de-inline-bl de-sum">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input data-num="{num}" id="<?=$prefix?>_sum_{num}" disabled type="text" class="sumInput ui-ctl-element" placeholder="сумма" value="{sum}">
        </div>
    </div>

    <div class="de-inline-bl ">
        <button data-num="{num}" id="<?=$prefix?>_addbi_{num}" disabled class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm">Еще</button>
    </div>

    <div class="de-inline-bl">
        <button data-num="{num}" id="<?=$prefix?>_delbi_{num}" class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X</button>
    </div>

</div>

<?
$templateItem = ob_get_contents();
ob_end_clean();


$items = '';
$arItemSumJs = [];
$arItemPsntJs = [];

$arReplacement = ["{num}", "{bi}", "{psnt}", "{sum}", "{selects}" , "{value}"];
$baseItem = str_replace(["wrap_", "be-item-hide"], "", $templateItem);

if(!empty($arResult['DATA_BIZ_UNIT_PRODUCTS'])){
    $countItems = 0;
    foreach ($arResult['DATA_BIZ_UNIT_PRODUCTS'] as $key=>$arItem){
        $selects = "";
        $value = 0;

        $displaySwitch = '';
        if(!empty($arResult['ALL_PRODUCTS'][$arResult['BI_ID']])){
            foreach ($arResult['ALL_PRODUCTS'][$arResult['BI_ID']] as $arProduct) {
                $selected = '';
                if($arItem['ID'] == $arProduct['ID']){
                    $selected = 'selected';
                    $value = $arItem['ID'];
                }
                //echo "<pre>"; print_r($nameBi); echo "</pre>";
                $selects .= '<option class = "'.$prefix.'" value = "'.$arProduct['ID'].'" '.$selected.'>'.$arProduct['NAME'].'</option>';
                //$selects .= "<option class=\"".$prefix."-options\" value=\"".$arProduct['ID']."\" ".">".$arProduct['NAME']."</option>";
            };

            //$arItem['SUM'] = number_format($arItem['SUM'], 2, ".", "");
            //$arItem['PSNT'] = number_format($arItem['PSNT'], 2, ".", "");

            $arReplace = [$key, $arItem['BIZ_UNIT_PRODUCT_ID'],  $arItem['PSNT'], $arItem['SUM'], $selects ,$value];

            if($_REQUEST['ajax']){
                $arReplace[0][] = '0';
            }

            $item = str_replace($arReplacement, $arReplace, $baseItem);
            if($countItems > 0){
                $displaySwitch = 'none';
                $item = str_replace('{displaySwitch}', $displaySwitch, $item);
            }

            $items .= $item;
            $countItems++;
            $hideItem = str_replace("{selects}", $selects, $templateItem);
            $hideItem = str_replace(["{bi}", "{psnt}", "{sum}", "selected"], "", $hideItem);
        }
    }
}else{
    if(!empty($arResult['ALL_PRODUCTS'][$arResult['BI_ID']])){
        foreach ($arResult['ALL_PRODUCTS'][$arResult['BI_ID']] as $arProduct) {
            $selected = '';
            //echo "<pre>"; print_r($nameBi); echo "</pre>";
            $selects .= '<option class = "'.$prefix.'" value = "'.$arProduct['ID'].'" '.$selected.'>'.$arProduct['NAME'].'</option>';
            //$selects .= "<option class=\"".$prefix."-options\" value=\"".$arProduct['ID']."\" ".">".$arProduct['NAME']."</option>";
        };

        $biItem['SUM'] = number_format(0, 2, ".", "");
        $biItem['PSNT'] = number_format(0, 2, ".", "");


        $arReplace = [0, $biItem['BIZ_UNIT_PRODUCT_ID'],  $biItem['PSNT'], $biItem['SUM'], $selects ,0];

        if($_REQUEST['ajax']){
            $arReplace[0][] = '0';
        }

        $item = str_replace($arReplacement, $arReplace, $baseItem);
        $items .= $item;

        $hideItem = str_replace("{selects}", $selects, $templateItem);
        $hideItem = str_replace(["{bi}", "{psnt}", "{sum}", "selected"], "", $hideItem);
    }
}

/*
foreach ($arResult['AR_BIZ_UNIT_PRODUCTS']  as $key => $biItem){
    $selects = "";
    foreach ($arResult['ALL_BIZ_UNIT_PRODUCTS'] as $idBi => $nameBi) {
       //echo "<pre>"; print_r($nameBi); echo "</pre>";
        $selected = ($idBi == $biItem['BIZ_UNIT_PRODUCT_ID'] ? "selected" : "");
        $selects .= "<option class=\"".$prefix."-options\" value=\"".$idBi."\" ".$selected.">".$nameBi."</option>";
    };

    $biItem['SUM'] = number_format($biItem['SUM'], 2, ".", "");
    $biItem['PSNT'] = number_format($biItem['PSNT'], 2, ".", "");


    $arReplace = [$key, $biItem['BIZ_UNIT_PRODUCT_ID'],  $biItem['PSNT'], $biItem['SUM'], $selects];
    $item = str_replace($arReplacement, $arReplace, $baseItem);
    $items .= $item;

    if($key == 0){
        $arItemSumJs[] = $arResult['ALL_SUM'];
        $arItemPsntJs[] = $arResult['ALL_PSNT'];
    } else {
        $arItemSumJs[] = $biItem['SUM'];
        $arItemPsntJs[] = $biItem['PSNT'];
    }
    $countItems++;
};

$hideItem = str_replace("{selects}", $selects, $templateItem);
$hideItem = str_replace(["{bi}", "{psnt}", "{sum}", "selected"], "", $hideItem);
*/
?>

<div class="be_product-form" id="<?=$prefix?>-choose-be-form">
    <?=$items?>
    <?=$hideItem;?>
</div>
