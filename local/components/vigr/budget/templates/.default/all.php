<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
    "immo:budget.list",
    "ag.grid",
    [
        'EXCEL_PARAMS' => [
            'type' => 'all',
            'id' => ''
        ]
    ],
    $component
);
?>
