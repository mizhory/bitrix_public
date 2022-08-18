<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    // Название действия для конструтора.
    'NAME' => '[Immo] Подстановка в значений в шаблон строки',

    // Описание действия для конструктора.
    'DESCRIPTION' => 'Принимает шаблон строки со значениями. Возвращает новую строку со значениями в строке. Если значение не найдено, подставляется "-"',

    // Тип: “activity” - действие, “condition” - ветка составного действия.
    'TYPE' => 'activity',

    // Название класса действия без префикса “CBP”.
    'CLASS' => 'SprintFString',

    // Название JS-класса для управления внешним видом и поведением в конструкторе.
    // Если нужно только стандартное поведение, указывайте “BizProcActivity”.
    'JSCLASS' => 'BizProcActivity',

    // Категория действия в конструкторе.
    'CATEGORY' => array(
        'ID' => 'document',
    ),

    'RETURN' => [
        'stringFinal' => [
            'NAME' => 'Итоговая строка',
            'TYPE' => FieldType::STRING
        ],
    ]
);
?>