<?php
?>

<option value = 0>Не выбрано</option>

<?foreach ($arResult['DATA']['IBLOCKS'] as $arIblock):?>
    <option <?if($arIblock['ID'] === $arParams['userField']['VALUE']):?>selected<?endif;?> value="<?=$arIblock['ID']?>"><?=$arIblock['NAME']?></option>
<?endforeach;?>
