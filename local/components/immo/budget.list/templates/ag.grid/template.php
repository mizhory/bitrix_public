<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

if (!empty($arResult['DETAIL_BE'])) {
    $APPLICATION->SetTitle("{$arResult['DETAIL_BE']['NAME']} ({$arResult['DETAIL_BE']['CURRENCY']})");
}

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleFlexibleSpace = "lists-pagetitle-flexible-space";
$pagetitleAlignRightContainer = "lists-align-right-container";
if ($isBitrix24Template) {
    $bodyClass = $APPLICATION->GetPageProperty("BodyClass");
    $APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."pagetitle-toolbar-field-view");
    $this->SetViewTarget("inside_pagetitle");
    $pagetitleFlexibleSpace = "";
    $pagetitleAlignRightContainer = "";
} elseif (!IsModuleInstalled("intranet")) {
    $APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-flexible-space <?=$pagetitleFlexibleSpace?>">
    <? $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
        'FILTER_ID' => $arResult['FILTER_ID'],
        'FILTER' => $arResult['FILTER_FIELDS'],
        'ENABLE_LIVE_SEARCH' => false,
        'ENABLE_LABEL' => true
    ], null, ['HIDE_ICONS' => 'Y']
    ); ?>
</div>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
    <input type="hidden" value="" id='type'>
    <span id="excel-download" class="ui-btn ui-btn-light-border ui-btn-themes ui-btn-icon-print">Выгрузить в excel</span>
</div>
<?
if ($isBitrix24Template) {
    $this->EndViewTarget();
}

$APPLICATION->IncludeComponent('immo:ag.grid', '', [
    'ID' => 'table-wrapper',
    'COLUMNS' => $arResult['COLUMNS'],
    'WIDTH' => '100%',
    'HEIGHT' => '700px'
]);

?>

<script type="text/javascript">
    BX(function () {
        const list = new BudgetList({
            id: 'table-wrapper',
            signedParams: '<?=$arResult['SIGNED_PARAMS']?>',
            excelParams: <?=\CUtil::PhpToJSObject($arParams)?>
        });
        list.init();
    });
</script>

