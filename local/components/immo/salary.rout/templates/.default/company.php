<?php

use Bitrix\Main\Loader;
use Immo\Statements\View\View;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loader::includeModule('immo.statements');

$view = new View($arParams, $arResult);
$view->render();