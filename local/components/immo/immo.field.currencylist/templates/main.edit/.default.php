<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CurrencyListUfComponent $component
 */

\Bitrix\Main\UI\Extension::load(['ui.buttons', 'ui.forms']);

$multiple = $arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple ' : '';
?>
<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
    <div class="ui-ctl-after ui-ctl-icon-angle"></div>
    <select <?=$multiple?>  class="ui-ctl-element name="<?=$arResult['additionalParameters']['NAME']?>" id="">
        <option></option>
    <?php foreach (array_keys($arResult['CURRENCY_LIST']) as $currency):?>
        <option <?= in_array($currency, $arResult['value'], true) ? 'selected' : ''?> value="<?=$currency?>"><?=$currency?></option>
    <?php endforeach; ?>

    </select>
</div>
