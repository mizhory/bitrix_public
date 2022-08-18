<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER, $APPLICATION;

$this->addExternalJs($templateFolder . '/ag-grid/ag-grid-community.min.noStyle.js');
$this->addExternalCss($templateFolder . '/ag-grid/ag-grid.css');
$this->addExternalCss($templateFolder . '/ag-grid/ag-theme-alpine.css');

$ajaxId = CAjax::getComponentID('bitrix:main.ui.grid', '.default', '');
echo '<pre>';
print_r($arResult['filterId']);
echo '</pre>';
$gridId = "grid_{$arResult['filterId']}";

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleFlexibleSpace = "lists-pagetitle-flexible-space";
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
    $bodyClass = $APPLICATION->GetPageProperty("BodyClass");
    $APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."pagetitle-toolbar-field-view");
    $this->SetViewTarget("inside_pagetitle");
    $pagetitleFlexibleSpace = "";
    $pagetitleAlignRightContainer = "";
}
elseif(!IsModuleInstalled("intranet"))
{
    $APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-flexible-space <?=$pagetitleFlexibleSpace?>">
    <? $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
        'FILTER_ID' => $arResult['filterId'],
        'GRID_ID' => $gridId,
        'FILTER' => $arResult['filterFields'],
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
if($isBitrix24Template)
{
    $this->EndViewTarget();
}

?>

<div class="grid-table-custom">
    <div id="table-wrapper" class="ag-theme-alpine bitrix24-grid-theme" style="height: 700px; width:100%;"></div>
</div>

<script type="text/javascript">

    BX(function () {
        const list = new BudgetList({
            id: 'table-wrapper',
            columns: <?=\CUtil::PhpToJSObject($arResult['columns'])?>,
            data: <?=\CUtil::PhpToJSObject($arResult['data'])?>,
            componentName: '<?=$arParams['componentName']?>',
            jsParams: <?=\CUtil::PhpToJSObject($arParams['jsParams'] ?? [])?>,
            signedParams: '<?=$arResult['signedParams']?>',
            excelParams: <?=\CUtil::PhpToJSObject($arParams['excelParams'])?>
        });
        list.init();
    });
</script>

