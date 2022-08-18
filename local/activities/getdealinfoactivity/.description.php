<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => 'Получить инфо по БЕ',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Получить элемент',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GetDealInfoActivity',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'curse'=>[
            'NAME'=>'Курс валюты',
            'TYPE'=>FieldType::STRING
        ],
        'rate'=>[
            'NAME'=>'Валюта плательщика',
            'TYPE'=>FieldType::STRING
        ],
        'article'=>[
            'NAME'=>'Статья расходов',
            'TYPE'=>FieldType::STRING
        ],
        'urL'=>[
            'NAME'=>'Плательщик',
            'TYPE'=>FieldType::STRING
        ],
        'rateBe'=>[
            'NAME'=>'Валюта БЕ',
            'TYPE'=>FieldType::STRING
        ]
    ]
);
?>