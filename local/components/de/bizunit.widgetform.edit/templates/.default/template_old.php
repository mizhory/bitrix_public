<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Application;

\Bitrix\Main\UI\Extension::load("ui.forms");

$prefix = $arParams['PREFIX_ID'];

ob_start();

?>
<div class="be-item be-item-hide" id="<?=$prefix?>_wrap_item_{num}">
    <div class="de-inline-bl de-bi">

        <div class="ui-entity-editor-block-title de-block de-label">
            <label class="ui-entity-editor-block-title-text">Бизнес единица</label>
        </div>

        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <select value="{bi}" data-num="{num}" id="<?=$prefix?>_selbi_{num}" class="ui-ctl-element select-bizunit" onchange="bizUnitWidgetForm_<?=$prefix?>.loadBizUnitProducts(this, {num}); bizUnitWidgetForm_<?=$prefix?>.setActiveSaveBtn(this)">
                <option value="">не выбрана</option>{selects}
            </select>
        </div>

    </div>

    <div class="de-inline-bl de-psnt">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text">% затрат</label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input data-num="{num}" id="<?=$prefix?>_psnt_{num}" type="text" class="ui-ctl-element" onchange="bizUnitWidgetForm_<?=$prefix?>.setSumByPsnt(this)" value="{psnt}">
        </div>

    </div>

    <div class="de-inline-bl de-sum">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text">Сумма</label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input data-num="{num}" id="<?=$prefix?>_sum_{num}" type="text" class="ui-ctl-element" placeholder="сумма" onchange="bizUnitWidgetForm_<?=$prefix?>.setPsntBySum(this)" value="{sum}">
        </div>
    </div>

    <div class="de-inline-bl ">
        <button data-num="{num}" id="<?=$prefix?>_savebi_{num}" class="ui-btn ui-btn-success ui-ctl-block ui-btn-sm" onclick="/*bizUnitWidgetForm_<?//=$prefix?>.saveItem(this)*/">Сохранить</button>
    </div>

    <div class="de-inline-bl">
        <button data-num="{num}" id="<?=$prefix?>_delbi_{num}" class="ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm" onclick="bizUnitWidgetForm_<?=$prefix?>.deleteItem(this)">X</button>
    </div>

    <div data-num="{num}" style='display:none' id="<?=$prefix?>_warning_{num}" class='warning'>Не хватает бюджета. Доступно: <span id="<?=$prefix?>_warningsum_{num}">100</span></div>

    <div id="<?=$prefix?>_product_item_{num}" class='product-section'>{product}</div>
</div>

<?
$templateItem = ob_get_contents();
ob_end_clean();

$arReplacement = ["{num}", "{bi}", "{psnt}", "{sum}", "{selects}"];
$baseItem = str_replace(["wrap_", "be-item-hide"], "", $templateItem);

$arItemSumJs = [];
$arItemPsntJs = [];

$countItems = 0;
$optionSelected = [];

foreach ($arResult['BIZ_UNITS_DATA']  as $key => $biItem){
    $selects = "";

    $selected = '';

    $selects .= "<option value=\"".$biItem['BI_ID']."\" ".$selected.">".$biItem['BI_NAME']."</option>";

    /*
    foreach ($arResult['AR_BIZ_UNIT'] as $idBi => $nameBi) {
        $selected = ($idBi == $biItem['BI_ID'] ? "selected" : "");
        $selects .= "<option value=\"".$idBi."\" ".$selected.">".$nameBi."</option>";
    };
    */

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

$hideItem = str_replace("{selects}", $selects, $templateItem);
$hideItem = str_replace(["{bi}", "{psnt}", "{sum}", "selected"], "", $hideItem);
?>

<div class="be-form" id="<?=$prefix?>-choose-be-form">

    <div class="be-item" id="<?=$prefix?>_sum">

        <div class="de-inline-bl de-allsum">
            <div class="ui-entity-editor-block-title de-block de-label">
                <label class="ui-entity-editor-block-title-text">Сумма и валюта</label>
            </div>

            <input type="hidden" name="OPPORTUNITY" value="<?=$arResult['ALL_SUM_FORMAT']?>">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block"> <!-- 1. Основной контейнер -->
                <input name="UF_CRM_BIZUINIT_WIDGET" onchange="bizUnitWidgetForm_<?=$prefix?>.setOpportynity(this.value); " value="<?=$arResult['ALL_SUM_FORMAT']?>" type="text" class="ui-ctl-element">  <!-- 2. Основное поле -->
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

                <select onchange="bizUnitWidgetForm_<?=$prefix?>.setCurrency(this.value);" value="{bi}" data-num="{num}" id="<?=$prefix?>_selbi_{num}" class="ui-ctl-element" onchange="bizUnitWidgetForm_<?=$prefix?>.loadBizUnitProducts(this, {num}); bizUnitWidgetForm_<?=$prefix?>.setActiveSaveBtn(this)">
                    <option value="">не выбрана</option>
                    <?php
                    foreach($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                        $selected = "";
                        if($arResult['ALL_SUM_CURRENCY_ID'] == $id){
                            $selected = ' selected';
                        }
                        ?><option value="<?=$id?>" <?=$selected?>><?=$elemCurrency?></option><?
                    endforeach;

                    ?>
                </select>
            </div>

        </div>

    </div>

    <?=$items?>
    <?=$hideItem;?>
</div>


<script>
    var bizUnitWidgetForm_<?=$prefix?> = new bizUnitForm({
        prefix : '<?=$prefix?>',
        allSum : <?=$arResult['ALL_SUM']?>,
        allPsnt : 100,
        ietmMaxPsnt : <?=CUtil::PhpToJSObject($arItemPsntJs, false, false, true)?>,
        ietmMaxSum : <?=CUtil::PhpToJSObject($arItemSumJs, false, false, true)?>,
        countItems : <?=$countItems?>,
        budgetBizUnit : <?=CUtil::PhpToJSObject($arResult['AR_BIZ_UNIT_BUDGET'], false, false, true)?>
    });
    bizUnitWidgetForm_<?=$prefix?>.hideFerstDelBtn("<?=$prefix?>_delbi_0");

</script>