<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->setTitle('История бюджета');
$APPLICATION->IncludeComponent(
    "vigr:table",
    "history",
    [
        'filterId' => $arResult['filterId'],
        'data' => $arResult['data'],
        'filterFields'=>$arResult['filterFields']
    ],
    $component
);
