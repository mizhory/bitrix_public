<?php

use Immo\Component\Motivation;
use Immo\Iblock\Manager;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 * @var $component Motivation
 *
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// проверка на доступы
$iIblockId = Manager::getIblockId('motivation');
$arFilter = [
    'IBLOCK_ID' => $iIblockId,
    'ID' => (int)$arResult['VARIABLES']['ID'],
    'ACTIVE' => 'Y',
    'CHECK_PERMISSIONS' => 'Y',
    "MIN_PERMISSION" => "R",
];
$res = CIBlockElement::GetList(
    [],
    $arFilter,
    false,
    false,
    [
        'ID',
        'IBLOCK_ID',
        'PROPERTY_' . 'F_MONTH',
        'PROPERTY_' . 'F_YEAR',
        'PROPERTY_' . 'SELECTED_BE.NAME',
    ]
);
if ($arElem = $res->Fetch()) {
    // формирование имя ссылки
    $iMonth = (int)array_search(
        $arElem['PROPERTY_F_MONTH_VALUE'], [
        5 => "Май",
        6 => "Июнь",
        7 => "Июль",
        8 => "Август",
        9 => "Сентябрь",
        10 => "Октябрь",
        11 => "Ноябрь",
        12 => "Декабрь",
        1 => "Январь",
        2 => "Февраль",
        3 => "Март",
        4 => "Апрель",
    ]);

    if ($iMonth <= 10) {
        $iMonth = '0' . $iMonth;
    }
    $sTitle = '№' . $arElem['ID'] . ' ' . $arElem['PROPERTY_SELECTED_BE_NAME'] . ' от ' . $iMonth . '.' . $arElem['PROPERTY_F_YEAR_VALUE'];
    $APPLICATION->IncludeComponent(
        'immo:logs.state.list',
        '',
        [
            'ID' => $arResult['VARIABLES']['ID'],

            'BACK_NAME_LINK' => 'Вернуться к премиальной ведомости ' . $sTitle,
            'BACK_URL' => '/sheets/motivation/' . $arElem['ID'] . '/',

            'BACK_LIST_NAME_LINK' => 'Вернуться к списку всех ведомостей',
            'BACK_LIST_URL' => '/sheets/motivation/',
        ],
        $component,
        ['HIDE_ICONS' => 'Y'],
    );
} else {
    ShowError('У вас нет прав на просмотр ведомсти');
}
