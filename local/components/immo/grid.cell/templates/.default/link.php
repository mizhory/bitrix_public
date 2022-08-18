<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;

/**
 * @var array $arParams
 * @var array $arResult
 */

Extension::load(['ui.buttons']);
?>

<form action="<?=$arResult['action']?>" method="post">
    <?php foreach ($arResult['post_data'] as $name => $value):?>
        <input type="hidden" name="<?=$name?>" value="<?=$value?>">
    <?php endforeach; ?>
    <button type="submit" class="ui-btn ui-btn-light ui-btn-no-caps"><?=$arResult['button_text']?></button>
</form>
