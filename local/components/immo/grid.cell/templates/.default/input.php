<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;

/**
 * @var array $arParams
 * @var array $arResult
 */
// ширина инпута > ширины заголовка, но < 12 символов
Extension::load(['ui.forms']);
?>


    <input
        type="<?=$arResult['input_type']?>"
        step="<?=$arResult['step']?>"
        name="<?=$arResult['field']?>"
        id="<?=sprintf('%s_%d', $arResult['field'], $arResult['row_id'])?>"
        data-row-id="<?=$arResult['row_id']?>"
        class="ui-ctl ui-ctl-textbox ui-ctl-element grid-editable-field"
        value="<?=$arResult['value']?>"
    >

