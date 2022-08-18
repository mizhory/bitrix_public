<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Список согласующих по стадии с проверками',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Достает список согласующих по стадии (с проверкой по увольнению/отпуску/согласовавший ранее). Для финансовых заявок. Инфоблок',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GetUsersForStageComplexWithCheck',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'userStr'=>[
            'NAME'=>'Список согласующих по стадии с проверкой (разделитель - запятая)',
            'TYPE'=>FieldType::USER
        ],
        'userArray'=>[
            'NAME'=>'Список согласующих по стадии с проверкой (массив)',
            'TYPE'=>FieldType::USER
        ]
    ]
);
?>