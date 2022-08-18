<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$view = new \Immo\Statements\View\View($arParams, $arResult);
$view->renderDetails();