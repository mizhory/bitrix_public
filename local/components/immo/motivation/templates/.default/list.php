<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 *
 */


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<?php
$APPLICATION->IncludeComponent(
    'immo:motivation.list',
    '',
    [
        'CAN_READ' => $arResult['CAN_READ'],
        'CAN_CREATE' => $arResult['CAN_CREATE'],
    ],
    null,
    ['HIDE_ICONS' => 'Y'],
);
?>
<?php


