<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 *
 */
$arResult["EXCEL_COLUMN_NAME"] = [];
$arResult["EXCEL_CELL_VALUE"] = [];
foreach ($arResult['COLUMNS'] as $arColumn) {
    $arResult["EXCEL_COLUMN_NAME"][$arColumn['id']] = $arColumn['name'];
}
$arKeys = array_keys($arResult["EXCEL_COLUMN_NAME"]);
foreach ($arResult['MOTIVATION_LIST'] as $arElement) {
    $arElement['data']['TO_USERS'] =  $arElement['columns']['TO_USERS'];
    $arElement['data']['DONE_USERS'] =  $arElement['columns']['DONE_USERS'];
    $arElement['data']['ASSIGNED_BY'] =  $arElement['columns']['ASSIGNED_BY'];
    $arResult["EXCEL_CELL_VALUE"][] = array_combine($arKeys, $arElement['data']);
}
$APPLICATION->RestartBuffer();
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: filename=motivation.xls");
?>
<html>
<head>
    <title><? $APPLICATION->GetTitle() ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset='<?= LANG_CHARSET ?>">
    <style>
        td {
            mso-number-format: \@;
        }

        .number0 {
            mso-number-format: 0;
        }

        .number2 {
            mso-number-format: Fixed;
        }
    </style>
</head>
<body>
<table border="1">
    <tr>
        <? foreach ($arResult["EXCEL_COLUMN_NAME"] as $value) {
            ?>
            <td><?= $value; ?></td>
            <?
        }
        ?>
    </tr>

    <? foreach ($arResult["EXCEL_CELL_VALUE"] as $array) {
        ?>
        <tr>
            <? foreach ($array as $value) {
                ?>
                <td><?= $value; ?></td>
                <?
            }
            ?>
        </tr>
        <?
    }
    ?>
</table>
</body>
</html>
<?php
$r = $APPLICATION->EndBufferContentMan();
echo $r;
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
die();
?>