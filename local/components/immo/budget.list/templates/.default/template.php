<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->setTitle('Общий бюджет');

$APPLICATION->IncludeComponent(
    'vigr:table',
    'ag.grid',
    [
        'filterFields'=>$arResult['filterFields'],
        'filterId'=>$arResult['filterId'],
        'filterData'=>$arResult['filterData'],
        'length'=>$arResult['length'],
        'type'=>'all',
        'data'=>$arResult['data'],
        'rates'=>$arResult['rates'],
        'NEED_RATE'=>'Y',
        'budgetRows' => $arResult['BUDGET_ROWS'],
        'componentName' => $this->getComponent()->getName()
    ]
);?>
