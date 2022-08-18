<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var StatementsDetailsComponent $component
 *
 * @global CMain $APPLICATION
 */

Extension::load(['ui.buttons', 'ui.forms']); ?>

<div class="detail-statements-main-info">
    <p>
        <?php foreach ($arResult['views'] as $view) {
            echo sprintf('%s: %s<br>', $view['label'], $view['value']);
        }?>
    </p>
    <?php if($arResult['show_selector']):?>

        <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <select
                    data-element-id="<?=$arParams['element_id']?>"
                    id="UF_TOTAL_SUM_CALCULATION_TYPE_GENERAL"
                    name="UF_TOTAL_SUM_CALCULATION_TYPE"
                    class="ui-ctl-element"
            >
                <option></option>
                <?php foreach($arResult['selector_items'] as $id => $value):?>
                <option
                        <?= (int) $arResult['total_sum_calculation_type_id'] === (int) $id ? 'selected': ''?>
                        value="<?=$id?>"><?=$value?></option>
                <?php endforeach;?>
            </select>
        </div>

    <?php endif;?>
</div>
<?php

$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', $arResult['GRID']);
$gridId = CUtil::PhpToJSObject($arResult['GRID']['GRID_ID']);
$role = CUtil::PhpToJSObject($arParams['role']);

if(!is_null($arResult['workflow_id'])):
?>
<div class="detail-statements-main-info">
    <a href="javascript::void()" id="approval-history">Просмотреть историю согласования</a>
</div>
<?php endif;?>

<script>
    BX.ready(function () {
        StatementsDetails.init(<?=$arResult['js_options']?>);
    })
</script>
