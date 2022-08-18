<?php

use Immo\Statements\View\View;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$view = new View($arParams, $arResult);
$view->renderDetails();