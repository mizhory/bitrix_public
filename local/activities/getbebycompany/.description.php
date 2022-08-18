<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Получить БЕ по юр. лицу',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'На вход принимает ID юрлица из старого списка. Возвращает ID БЕ из оргструктуры по привязанному юр. лицу',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GetBeByCompany',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'id' => [
            'NAME' => 'ID бизнес единицы',
            'TYPE' => FieldType::STRING
        ],
        'oldBeId' => [
            'NAME' => 'ID бизнес единицы (в старом списке)',
            'TYPE' => FieldType::STRING
        ],
        'beName' => [
            'NAME' => 'Название бизнес единицы',
            'TYPE' => FieldType::STRING
        ],
    ]
);
?>