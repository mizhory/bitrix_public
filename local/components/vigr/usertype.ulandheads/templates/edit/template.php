<?php

use Bitrix\Main\UI\Extension;

Extension::load('ui.bootstrap4');
$arNames = $arResult['names'];

global $USER;

?>

<? if ($arResult['logData']): ?>
    <input type="hidden" id='logValue' value='<?= json_encode($arResult['logData']) ?>'>
<? endif ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.0/slimselect.min.css" rel="stylesheet"></link>
<input type="hidden" id='mainValue' value='<?= json_encode($arResult['mainData']) ?>'>
<input id="mainFieldUl" name="<?= $arParams['userField']['FIELD_ID'] ?>">
<table style="text-align: center" class="table table-bordered">
    <thead>
    <th colspan="3">
        <select class = 'main_user'>
            <?foreach ($arResult['user'] as $key=>$user):?>
                <option <?if($USER->getID() == $key):?>selected<?endif;?> value = '<?=$key?>'><?=$user?></option>
            <?endforeach;?>
        </select>
    </th>
    </thead>
    <tbody class = 'body'>
    <?include 'ur.php'?>
    </tbody>
</table>

<input type="hidden" value="<?= bitrix_sessid_get() ?>">


