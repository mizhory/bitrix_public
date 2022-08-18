<?php
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if(!$_REQUEST['listId']){
    die('error');
}

$dbProps = CIBlockProperty::GetList(
    [],
    [
        'IBLOCK_ID'=>$_REQUEST['listId']
    ]
);

$arProps = [];

while($arProp = $dbProps->fetch()){
    $arProps[] = $arProp;
}
?>

<?php ob_start()?>
<select class = 'propId' name = 'propId'>
    <?foreach ($arProps as $arProp):?>
        <option value="<?=$arProp['ID']?>"><?=$arProp['NAME']?></option>
    <?endforeach;?>
</select>

<?
echo json_encode([
    'content'=>ob_get_clean()
]);

?>


