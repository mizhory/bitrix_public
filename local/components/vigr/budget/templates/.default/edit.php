<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$APPLICATION->SetTitle('Изменение бюджета');

$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    [
        'POPUP_COMPONENT_NAME' => "vigr:budget.edit",
        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
        'POPUP_COMPONENT_PARAMS' => [
            'variables' => $arResult['VARIABLES'],
        ],
        "USE_PADDING" => false,
        "USE_UI_TOOLBAR" => "N",
    ],
    $this->getComponent()
);