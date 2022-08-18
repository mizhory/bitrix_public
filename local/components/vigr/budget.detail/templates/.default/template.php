<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$title = "Бюджет: {$arResult['nameBe']}";
if (array_key_exists($arResult['filterData']['biznesUnitName'], $arResult['rates'])) {
    $title .= " ({$arResult['rates'][$arResult['filterData']['biznesUnitName']]})";
}

$APPLICATION->setTitle($title);

$APPLICATION->IncludeComponent(
    "vigr:table",
    ".default",
    Array(
        'filterId'=>$arResult['filterId'],
        'filterFields'=>$arResult['filterFields'],
        'filterData'=>$arResult['filterData'],
        'data'=>$arResult['data'],
        'length'=>$arResult['length'],
        'type'=>'detail',
        'rates'=>$arResult['rates'],
        'budgetRows' => $arResult['BUDGET_ROWS']
    ),
    $component,
    ['HIDE_ICONS' => 'Y']
);
?>