<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return [
    'READ_ALL' => [
        'title' => Loc::getMessage("OP_NAME_READ_ALL"),
        'description' => '',
    ],
    'READ_CURRENT_STATE' => [
        'title' => Loc::getMessage("OP_NAME_READ_CURRENT_STATE"),
        'description' => '',
    ],
    'EDIT_CURRENT_STATE' => [
        'title' => Loc::getMessage("OP_NAME_EDIT_CURRENT_STATE"),
        'description' => '',
    ],
    'EDIT_ALL' => [
        'title' => Loc::getMessage("OP_NAME_EDIT_ALL"),
        'description' => '',
    ],
    'MANUAL_START' => [
        'title' => Loc::getMessage("OP_NAME_MANUAL_START"),
        'description' => '',
    ],
];