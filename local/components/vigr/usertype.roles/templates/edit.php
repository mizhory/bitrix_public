<?php

?>
<div>
    <select name="<?=$arParams['userField']['FIELD_NAME']?>">
        <?foreach ($arResult['DATA'] as $key=>$arData):?>
            <option <?if($key == $arParams['userField']['VALUE']):?>selected<?endif;?> value="<?=$key?>"><?=$arData?></option>
        <?endforeach;?>
    </select>
</div>
