<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    // Название действия для конструтора.
    'NAME' => '[Immo] Журналирование состояния процесса',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Сохранение состояния процесса в отдельной сущности',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'LogsState',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => [
        "ID" => "other",
    ],
];
?>