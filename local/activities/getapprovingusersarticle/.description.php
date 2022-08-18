<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    'NAME' => '[Immo] Получить список согласующих по статье бюджета',
    'DESCRIPTION' => 'Возвращает список согласующих по статье бюджета. С проверкой по листу увольнения/графику отстуствия/уже согласовавших',
    'TYPE' => 'activity',
    'CLASS' => 'GetApprovingUsersArticle',
    'JSCLASS' => 'BizProcActivity',
    'CATEGORY' => [
        'ID' => 'document',
    ],
    'RETURN' => [
        'arUsers'=>[
            'NAME'=>'Строка согласующих пользователей',
            'TYPE'=>FieldType::STRING
        ],
        'arUsersArray'=>[
            'NAME'=>'Массив согласующих пользователей',
            'TYPE'=>FieldType::USER
        ]
    ]
);
?>