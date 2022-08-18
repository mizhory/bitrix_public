<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
    'NAME' => '[Immo] Подсчет кол-во элементов в массиве',
    'DESCRIPTION' => 'Принимает на вход массив. Производит подсчет кол-во элементов в этом массиве',
    'TYPE' => 'activity',
    'CLASS' => 'CountElements',
    'JSCLASS' => 'BizProcActivity',
    'CATEGORY' => [
        'ID' => 'document',
    ],
    'RETURN' => [
        'count'=>[
            'NAME'=>'Кол-во элементов',
            'TYPE'=>FieldType::INT
        ]
    ]
);
?>