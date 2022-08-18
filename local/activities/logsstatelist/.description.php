<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    // Название действия для конструтора.
    'NAME' => '[Immo] Получить журнал состояний процесса',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Выводит журнал состояний процесса в нужном формате',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'LogsStateList',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => [
        "ID" => "other",
    ],

    'RETURN' => [
        'LogsStateList' => [
            'NAME' => 'Логирвоание строка',
            'TYPE' => FieldType::STRING
        ]
    ]
];
