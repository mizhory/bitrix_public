<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Получить пользователя (строка) по ID',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Возвращает строку пользователя в формате "user_{ID}". Нужен для при получения ID пользователей из настроек++ и дальнейшей передаче в бизнес-процесс',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GetUserBp',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'userString'=>[
            'NAME'=>'Пользователь строка (для бизнес процесса)',
            'TYPE'=>FieldType::USER
        ]
    ]
);
?>