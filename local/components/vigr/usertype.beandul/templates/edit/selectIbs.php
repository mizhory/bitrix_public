<?php
?>

<option value = 0>Не выбрано</option>

<?foreach ($arResult['DATA'] as $arData):?>
    <option <?if($arResult['PARAMS']['value'] == $arData['ID']):?>selected<?endif;?> value="<?=$arData['ID']?>"><?=$arData['NAME']?></option>
<?endforeach;?>
