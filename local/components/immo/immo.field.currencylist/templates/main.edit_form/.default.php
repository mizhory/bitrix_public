<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CurrencyListUfComponent $component
 */

$multiple = $arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple ' : '';
?>


    <select <?=$multiple?>  name="<?=$arResult['additionalParameters']['NAME']?>" id="">
        <option></option>
    <?php foreach (array_keys($arResult['CURRENCY_LIST']) as $currency):?>
        <option <?= in_array($currency, $arResult['value'], true) ? 'selected' : ''?> value="<?=$currency?>"><?=$currency?></option>
    <?php endforeach; ?>

    </select>

