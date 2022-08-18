<?php

use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var $APPLICATION CMain
 * @var $USER CUser
 *
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->addExternalJS('/local/modules/vigr.budget/js/imask.js');
Extension::load(['ui.buttons', 'ui.forms']);
$arHeaderTable = $arResult['arTableData']['arHeaderTable'];
$arRowsTable = $arResult['arTableData']['arRowsTable'];
$arFooterTable = $arResult['arTableData']['arFooterTable'];

$this->addExternalJs($templateFolder . '/ag-grid/ag-grid-community.min.noStyle.js');
$this->addExternalCss($templateFolder . '/ag-grid/ag-grid.css');
$this->addExternalCss($templateFolder . '/ag-grid/ag-theme-alpine.css');
CJSCore::Init(['currency']);
$currencyFormat = \CCurrencyLang::GetFormatDescription('RUB');
$currencyFormat['HIDE_ZERO'] = 'N';
$currencyFormat['DEC_POINT'] = ',';
$currencyFormat['THOUSANDS_SEP'] = ' ';
?>
<script type="text/javascript">
    BX.Currency.setCurrencyFormat('RUB', <? echo CUtil::PhpToJSObject($currencyFormat, false, true); ?>);
</script>
<form name="form_<?php echo $arResult['GRID']['GRID_ID']; ?>">
    <div id="block-info">
        <div id="page-header-statements" class="detail-statements-main-info">
            <div>
                <?php foreach ($arResult['views'] as $view) {
                    echo sprintf('%s: %s<br>', $view['label'], $view['value']);
                } ?>
            </div>
            <?php if ($arResult['show_selector']): ?>
                <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                    <select data-element-id="<?= $arParams['element_id'] ?>" id="UF_TOTAL_SUM_CALCULATION_TYPE_GENERAL"
                            name="UF_TOTAL_SUM_CALCULATION_TYPE"
                            class="ui-ctl-element js-uf_total_sum_calculation_type_general">
                        <option></option>
                        <?php
                        foreach ($arResult['selector_items'] as $id => $value) { ?>
                            <option <?= (int)$arResult['total_sum_calculation_type_id'] === (int)$id ? 'selected' : '' ?>
                                    value="<?= $id ?>"><?= $value ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>

            <?php endif; ?>
        </div>
    </div>
    <div class="grid-table-custom">
        <div id="table-wrapper" class="ag-theme-alpine bitrix24-grid-theme" style="height: 700px; width:100%"></div>
    </div>
</form>
<script type="text/javascript" charset="utf-8">
    BX.ready(() => {
        StatementsDetails.init(<?php echo $arResult['js_options']; ?>)
    });
</script>