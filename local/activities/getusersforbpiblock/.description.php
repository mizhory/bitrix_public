<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Получить согласующих по БЕ',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Возвращает согласующих для БЕ из инфоблока',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GetUsersForBPIblock',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'arElementsUsers'=>[
            'NAME'=>'Массив элементов списка',
            'TYPE'=>FieldType::STRING
        ],
        'countUsers'=>[
            'NAME'=>'Кол-во согласующих пользователей ',
            'TYPE'=>FieldType::STRING
        ],
        'elementId'=>[
            'NAME'=>'ID записи ',
            'TYPE'=>FieldType::STRING
        ]
    ]
);
?>