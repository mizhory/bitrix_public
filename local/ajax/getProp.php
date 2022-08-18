<?php
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$arElement = CIBlockElement::GetList(
    [],
    [
        'IBLOCK_ID'=>$_POST['iblockID'],
        'ID'=>$_POST['id']
    ],
    false,
    false,
    [
        'PROPERTY_TEKHNICHESKOE_POLE_SORTIROVKI'
    ]
)->fetch()['PROPERTY_TEKHNICHESKOE_POLE_SORTIROVKI_VALUE'];

echo json_encode(['content'=>$arElement]);