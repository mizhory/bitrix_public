<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return [
    'STATEMENTS_DENY' => [
        'title' => Loc::getMessage("TASK_NAME_STATEMENTS_DENY"),
        'description' => '',
    ],
    'STATEMENTS_LIMITED_READ' => [
        'title' => Loc::getMessage("TASK_NAME_STATEMENTS_LIMITED_READ"),
        'description' => '',
    ],
    'STATEMENTS_READ' => [
        'title' => Loc::getMessage("TASK_NAME_STATEMENTS_READ"),
        'description' => '',
    ],
    'STATEMENTS_LIMITED_EDIT' => [
        'title' => Loc::getMessage("TASK_NAME_STATEMENTS_LIMITED_EDIT"),
        'description' => '',
    ],
    'STATEMENTS_EDIT' => [
        'title' => Loc::getMessage("TASK_NAME_STATEMENTS_EDIT"),
        'description' => '',
    ],
    'STATEMENTS_MANUAL_START' => [
        'title' => Loc::getMessage("TASK_NAME_STATEMENTS_MANUAL_START"),
        'description' => '',
    ],
    'STATEMENTS_FULL' => [
        'title' => Loc::getMessage("TASK_NAME_STATEMENTS_FULL"),
        'description' => '',
    ],
];