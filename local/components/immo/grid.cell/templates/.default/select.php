<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;

/**
 * @var array $arParams
 * @var array $arResult
 */

Extension::load(['ui.forms']);

$value = $arResult['value'];
$items = $arResult['items'];
?>

<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
    <div class="ui-ctl-after ui-ctl-icon-angle"></div>
    <select class="ui-ctl-element grid-editable-field" data-be-id="<?=$arResult['be_id']?>" data-row-id="<?=$arResult['row_id']?>" name="<?=$arResult['field']?>">
        <option></option>
        <?php foreach($items as $id => $item):?>

        <option <?=$value === $item['id'] ? 'selected' : ''?> value="<?=$item['id']?>"><?=$item['value']?></option>
            <?php d($item['id']);?>
        <?php endforeach;?>
    </select>
</div>
