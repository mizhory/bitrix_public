<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\UI\Extension;

Extension::load(['ui.buttons', 'fx', 'popup']);

$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', $arResult['GRID']);
