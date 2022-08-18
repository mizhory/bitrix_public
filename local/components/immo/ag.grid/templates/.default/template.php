<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->addExternalJs($templateFolder . '/ag-grid/ag-grid-community.min.noStyle.js');
$this->addExternalCss($templateFolder . '/ag-grid/ag-grid.css');
$this->addExternalCss($templateFolder . '/ag-grid/ag-theme-alpine.css');

?>

<div class="grid-table-custom">
    <div
        id="<?=$arParams['ID']?>"
        class="ag-theme-alpine bitrix24-grid-theme"
        style="height: <?=$arParams['HEIGHT']?>; width: <?=$arParams['WIDTH']?>;"
    ></div>
</div>

<script type="text/javascript">
    BX(function () {
        window['<?=$arParams['ID']?>'] = new AgGridInstance({
            id: '<?=$arParams['ID']?>',
            columns: <?=\CUtil::PhpToJSObject($arParams['COLUMNS'])?>,
            data: <?=\CUtil::PhpToJSObject($arParams['DATA'])?>,
        });;
    });
</script>

