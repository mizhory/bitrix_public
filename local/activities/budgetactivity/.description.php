<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => 'Бюджет - действие с заявкой',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Получить элемент',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'BudgetActivity',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'status'=>[
            'NAME'=>'Статус - ok/error',
            'TYPE'=>FieldType::STRING
        ],
        'errors'=>[
            'NAME'=>'Ошибки',
            'TYPE'=>FieldType::STRING
        ]
    ]
);
?>