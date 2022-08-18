<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arCurrentValues */

use Bitrix\Iblock\IblockTable;

$oIblock = IblockTable::getList([
    'select' => ['ID', 'NAME'],
    'filter' => ['IBLOCK_TYPE_ID' => 'lists'],
]);

$aIblockValues = [];

while ($aIblockValue = $oIblock->fetch()) {
    $aIblockValues[$aIblockValue['ID']] = '['. $aIblockValue['ID'] . ']' . ' ' . $aIblockValue['NAME'];
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'Инфоблок списка для бизнес-процессов',
            'TYPE' => 'LIST',
            'VALUES' => $aIblockValues,
        ],
        'ELEMENT_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID элемента инфоблока',
            'TYPE' => 'STRING',
        ],
        'USER_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID Пользователя',
            'TYPE' => 'STRING',
        ],
    ],
];
