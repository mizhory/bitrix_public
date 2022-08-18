<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Application;

\Bitrix\Main\UI\Extension::load("ui.forms");




$prefix = $arParams['PREFIX_ID'];
?>

<span class="field-wrap fields boolean">
    <span class="field-item fields boolean">
        <input class="fields boolean" type="hidden" value="0" id = 'distribution_start_id' name="distribution_start_id">
            <label style="width: 230px">
                <input <?if($arResult['DATA_BIZ_UNIT']['mainEventDustrbution'] == 1):?>checked<?endif;?> type="checkbox" class = 'distribution_start' id = 'distribution_start' value="1" name="distribution_start">
                Распределить по всем в равных %
            </label>
    </span>
</span>

<?php
ob_start();

?>
<div class="be-item be-item-hide" data-budget = "{budget}" data-num = "{num}" data-value = "{bi}" id="<?=$prefix?>_wrap_item_{num}">
    <div class="de-inline-bl de-bi">
        <div class="ui-entity-editor-block-title de-block de-label">
            <label class="ui-entity-editor-block-title-text">Бизнес единица</label>
        </div>

        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <select value="{num}" id="<?=$prefix?>_selbi_{num}" class="ui-ctl-element select-bizunit">
                <option value="0">Не выбрано</option>{selects}
            </select>
        </div>
    </div>

    <div class="de-inline-bl de-psnt">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text">% затрат</label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input id="<?=$prefix?>_psnt_{num}" type="text" class="percentInput ui-ctl-element" value="{psnt}">
        </div>

    </div>

    <div class="de-inline-bl de-sum">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text">Сумма</label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input id="<?=$prefix?>_sum_{num}" type="text" class="sumInput ui-ctl-element" placeholder="сумма"  value="{sum}">
        </div>
    </div>

    <div class="de-inline-bl de-sum" style="display: none">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text">Бюджет</label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input id="<?=$prefix?>_budget_{num}" type="text" class="budgetInput ui-ctl-element" placeholder="сумма"  value="{budget}">
        </div>
    </div>

    <div class="de-inline-bl ">
        <button id="<?=$prefix?>_addbi_{num}" class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm" >Еще</button>
    </div>

    <div class="de-inline-bl">
        <button id="<?=$prefix?>_delbi_{num}" class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X</button>
    </div>

    <div style='display:none' id="<?=$prefix?>_warning_{num}" class='warning'>Не хватает бюджета</div>

    <div id="<?=$prefix?>_product_item_{num}" class='product-section'>{product}</div>
</div>

<?
$templateItem = ob_get_contents();
ob_end_clean();


$baseItem = str_replace(["wrap_", " be-item-hide"], "", $templateItem);

$arItemSumJs = [];
$arItemPsntJs = [];

$countItems = 1;
$optionSelected = [];
$arReplacement = ["{num}" ,"{psnt}" , "{sum}" , "{budget}" , "{bi}"];
$arReplacement[] = '{selects}';
if(empty($arResult['DATA_BIZ_UNIT']['ITEMS'])){
    $selects = '';
    foreach ($arResult['DATA_BIZ_UNIT']['ALL_ITEMS']  as $key => $biItem){
        $selected = "";
        $selects .= '<option data-budget="'.$biItem['BI_BUDGET'].'" value="'.$biItem['BI_ID'].'" '.$selected.'>'.$biItem['BI_NAME'].'</option>';
       // $selects .= "<option data-budget=$biItem["BI_BUDGET"] value=\"".$biItem['BI_ID']."\" ".$selected.">".$biItem['BI_NAME']."</option>";
    }
    $biItem['SUM'] = number_format($biItem['SUM'], 2, ".", "");
    $biItem['PSNT'] = number_format($biItem['PSNT'], 2, ".", "");

    $arReplace = [1,0,0,0,0,$selects];
    $item = str_replace($arReplacement, $arReplace, $baseItem);

    $items .= $item;
}else{
    $biItems = $arResult['DATA_BIZ_UNIT']['ALL_ITEMS'];
    foreach ($arResult['DATA_BIZ_UNIT']['ITEMS'] as $key=>$arItem){
        $start = false;
        $selects = '';
        $budget = 0;
        $selKey = 0;
        foreach ($biItems as $biItem) {
            $selected = "";
            if($key == $biItem['BI_ID']){
                $selected = 'selected';
                $selKey = $key;
                $budget = $biItem['BI_BUDGET'];
            }
            $selects .= '<option data-budget="'.$biItem['BI_BUDGET'].'" value="'.$biItem['BI_ID'].'" '.$selected.'>'.$biItem['BI_NAME'].'</option>';
        };

        $biItem['SUM'] = number_format($biItem['SUM'], 2, ".", "");
        $biItem['PSNT'] = number_format($biItem['PSNT'], 2, ".", "");

        //$arReplace = [$key, $biItem['BI_ID'],  $arItem['PSNT'], $arItem['SUM'], $selects,$countItems];
        $arReplace = [$countItems, $arItem['PSNT'],  $arItem['SUM'], $budget,$key, $selects,];
        $item = str_replace($arReplacement, $arReplace, $baseItem);
        $countItems++;

        if(empty($arItem['PRODUCTS'])){
            $arItem['PRODUCTS'] = [];
        }

       ob_start();
       $APPLICATION->IncludeComponent(
           "de:bizunit.products.widgetform.edit",
           "",
           [
               'BIZ_UNIT_KEY' => $key,
               'BIZ_UNIT_ID' => $arItem['BI_ID'],
               "START"=>$start,
               'ALL_SUM' => $arItem['SUM'],
               'ALL_PSNT' => 100,
               'JSON'=>$arResult['DATA_BIZ_UNIT']['forJS'],
               'ALL_PRODUCTS'=>$arResult['DATA_BIZ_UNIT']['PRODUCTS'],
               'DATA_BIZ_UNIT_PRODUCTS' => $arItem['PRODUCTS'],
               'PREFIX_ID' => 'biz_productunit'.$arItem['BI_ID'],
               'ENTITY_ID' => $arResult['ENTITY_ID'],
               'ENTITY_TYPE' => 'RPA_2',
           ],
           null
       );
       $itemProduct = ob_get_contents();
       ob_end_clean();

       $item = str_replace("{product}", $itemProduct, $item);

        $items .= $item;

    }
}

/*
foreach ($arResult['DATA_BIZ_UNIT']  as $key => $biItem){
    $selects = "";
    foreach ($arResult['AR_BIZ_UNIT'] as $idBi => $nameBi) {
        $selected = ($idBi == $biItem['BI_ID'] ? "selected" : "");
        $selects .= "<option value=\"".$idBi."\" ".$selected.">".$nameBi."</option>";
    };

    $biItem['SUM'] = number_format($biItem['SUM'], 2, ".", "");
    $biItem['PSNT'] = number_format($biItem['PSNT'], 2, ".", "");

    $arReplace = [$key, $biItem['BI_ID'],  $biItem['PSNT'], $biItem['SUM'], $selects];
    $item = str_replace($arReplacement, $arReplace, $baseItem);

    ob_start();
    $APPLICATION->IncludeComponent(
        "de:bizunit.products.widgetform.edit",
        "",
        [
            'BIZ_UNIT_KEY' => $key,
            'BIZ_UNIT_ID' => $biItem['BI_ID'],
            'ALL_SUM' => $biItem['SUM'],
            'ALL_PSNT' => 100,
            'DATA_BIZ_UNIT_PRODUCTS' => $biItem['DATA_BIZ_UNIT_PRODUCTS'],
            'PREFIX_ID' => 'biz_productunit'.$biItem['BI_ID'],
            'ENTITY_ID' => $entityId,
            'ENTITY_TYPE' => 'RPA_2',
        ],
        null
    );
    $itemProduct = ob_get_contents();
    ob_end_clean();


    $item = str_replace("{product}", $itemProduct, $item);
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
*/

$hideItem = str_replace("{selects}", $selects, $templateItem);
$hideItem = str_replace(["{bi}", "{psnt}", "{sum}", "selected" ,"{budget}"], "0", $hideItem);
?>

<div class="be-form" id="<?=$prefix?>-choose-be-form">

    <div class="be-item" id="<?=$prefix?>_sum">

        <div class="de-inline-bl de-allsum">
            <div class="ui-entity-editor-block-title de-block de-label">
                <label class="ui-entity-editor-block-title-text">Сумма и валюта</label>
            </div>

            <input type="hidden" name="OPPORTUNITY" value="<?=$arResult['ALL_SUM_FORMAT']?>">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block"> <!-- 1. Основной контейнер -->
                <input name="UF_CRM_BIZUINIT_WIDGET" value = "<?=$arResult['DATA_BIZ_UNIT']['allSum']?>" type="text" class="ui-ctl-element mainSum">  <!-- 2. Основное поле -->
                <input name="UF_CRM_BIZUINIT_WIDGET_DATA" value="" type="hidden">
            </div>
        </div>

        <div class="de-inline-bl de-bi">

            <div class="ui-entity-editor-block-title de-block de-label">
                <label class="ui-entity-editor-block-title-text"></label>
            </div>

            <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                <input type="hidden" name="CURRENCY_ID" value="<?=$arResult['ALL_SUM_CURRENCY_ID']?>">

                <select value="{bi}" data-num="{num}" id="<?=$prefix?>_selbi_{num}" class="currencySelect ui-ctl-element">
                    <option value="">не выбрана</option>
                    <?php
                    foreach($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                        $selected = "";

                        if($arParams['BIZ_UNITS_DATA']['currency'] == $id){
                            $selected = ' selected';
                        }
                        ?><option value="<?=$id?>" <?=$selected?>><?=$elemCurrency?></option><?
                    endforeach;

                    ?>
                </select>
            </div>


        </div>

        <div class="de-inline-bl free-sum" style="padding-top: 10px">
            <div class="ui-entity-editor-block-title de-block de-label">
                <label class="ui-entity-editor-block-title-text">Свободный остаток : </label>
            </div>

            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                <input value = '0' disabled name="UF_CRM_BIZUINIT_FREE" type="text" class="ui-ctl-element">
            </div>
        </div>

        <div class="de-inline-bl free-sum" style="padding-top: 10px">
            <div class="ui-entity-editor-block-title de-block de-label">
                <label class="ui-entity-editor-block-title-text">Остаток (%) : </label>
            </div>

            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                <input value = '0' disabled name="UF_CRM_BIZUINIT_FREE_PERCENT" type="text" class="ui-ctl-element">
            </div>
        </div>

        <!--
        <div class="de-inline-bl free-sum" style="padding-top: 10px">
            <div class="ui-entity-editor-block-title de-block de-label">
                <label class="ui-entity-editor-block-title-text">Пересчет на основе (при изменении суммы) : </label>
            </div>

            <input type = 'radio' class = 'priority' value = 'percent' checked name = 'priority'>Проценты
            <input type = 'radio' class = 'priority' value = 'sum' name = 'priority'>Сумма
        </div>
        -->


    </div>

    <?=$items?>
    <?=$hideItem;?>
</div>
<?//ec($arResult['DATA_BIZ_UNIT']['forJS'],1,1)?>
<?if($arResult['DATA_BIZ_UNIT']['forJS']):?>
    <input type="hidden" class = 'forJs' value = '<?=json_encode($arResult['DATA_BIZ_UNIT']['forJS'])?>'>
<?endif?>
<script>
    if(false){
        var initForm  = false;
        var bizUnitWidgetForm_<?=$prefix?> = new bizUnitForm({
            prefix : '<?=$prefix?>',
            allSum : 0,
            allPsnt : 100,
            ietmMaxPsnt : <?=CUtil::PhpToJSObject($arItemPsntJs, false, false, true)?>,
            ietmMaxSum : <?=CUtil::PhpToJSObject($arItemSumJs, false, false, true)?>,
            countItems : <?=$countItems?>,
            budgetBizUnit : <?=CUtil::PhpToJSObject($arResult['AR_BIZ_UNIT_BUDGET'], false, false, true)?>
        });
        //bizUnitWidgetForm_<?//=$prefix?>.init("<?//=$prefix?>_delbi_0");
    }

</script>