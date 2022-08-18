<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Резервирование бюджета (инфоблок)',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Резервирование бюджета финансовой заявки',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'ReserveBudgetIblock',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'reserveStatus' => [ 
            'NAME' => 'Статус резервирования',
            'TYPE' => FieldType::STRING
        ],
        'errorMessage' => [
            'NAME' => 'Сообщения об ошибках резервирования',
            'TYPE' => FieldType::STRING
        ],
    ]
);
?>