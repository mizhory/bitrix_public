<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
    "immo:budget.list",
    "ag.grid",
    [
        'FILTER' => [
            ['biznesUnit', $arResult['VARIABLES']['ID']]
        ],
        'DETAIL_ID' => $arResult['VARIABLES']['ID'],
        'DETAIL' => 'Y',
        'EXCEL_PARAMS' => [
            'type' => 'detail',
            'id' => $arParams['beId']
        ]
    ],
    $component
);
