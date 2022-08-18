<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Сгенерировать ссылку на скачивание печати заявки',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'На вход принимает ID заявки и ID инфоблока заявки. Возвращает относительную ссылку на скачивание',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'GeneratePrintLinkIblock',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'linkPrint' => [
            'NAME' => 'Относительная ссылка на скачивание',
            'TYPE' => FieldType::STRING
        ]
    ]
);
?>