<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Получить инфо по БЕ (инфоблок)',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Возвращает информацию по БЕ, которые указаны в заявке',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GetDealInfoActivityIblock',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'be' => [
            'NAME' => 'Бизнес единицы',
            'TYPE' => FieldType::STRING
        ],
        'beName'=>[
            'NAME'=>'БЕ (имя)',
            'TYPE'=>FieldType::STRING
        ],
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
        'articleName'=>[
            'NAME'=>'Статья расходов (имя)',
            'TYPE'=>FieldType::STRING
        ],
        'url'=>[
            'NAME'=>'Плательщик',
            'TYPE'=>FieldType::STRING
        ],
        'rateBe'=>[
            'NAME'=>'Валюта БЕ',
            'TYPE'=>FieldType::STRING
        ],
        'ur'=>[
            'NAME'=>'Юр. лицо',
            'TYPE'=>FieldType::STRING
        ],
        'companyId'=>[
            'NAME'=>'ID Юр. лица из структуры',
            'TYPE'=>FieldType::STRING
        ],
        'urName'=>[
            'NAME'=>'Юр. лицо (имя)',
            'TYPE'=>FieldType::STRING
        ],
        'summa'=>[
            'NAME'=>'Сумма',
            'TYPE'=>FieldType::STRING
        ],
        'textTask'=>[
            'NAME'=>'Текст для задания',
            'TYPE'=>FieldType::STRING
        ],
        'rateId'=>[
            'NAME'=>'ID валюты плательщика',
            'TYPE'=>FieldType::STRING
        ],
        'beBank'=>[
            'NAME'=>'ID БЕ внутреннего банка',
            'TYPE'=>FieldType::STRING
        ],
        'bankName'=>[
            'NAME'=>'Наименование внутреннего банка',
            'TYPE'=>FieldType::STRING
        ],
        'products'=>[
            'NAME'=>'Продукты',
            'TYPE'=>FieldType::STRING
        ],
    ]
);
?>