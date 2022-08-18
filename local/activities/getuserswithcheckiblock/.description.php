<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Список юзеров с проверкой по увольнению, отсуствию и согласоваший ранее',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Список юзеров с проверкой по увольнению, отсуствию и согласоваший ранее',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GetUsersWithCheckIblock',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'userStr'=>[
            'NAME'=>'Список юзеров с проверкой',
            'TYPE'=>FieldType::USER
        ],
        'status'=>[
            'NAME'=>'Статус',
            'TYPE'=>FieldType::STRING
        ],
        'error'=>[
            'NAME'=>'Текст ошибки',
            'TYPE'=>FieldType::STRING
        ]
    ]
);
?>