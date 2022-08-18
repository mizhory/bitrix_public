<?php
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

Extension::load('ui.bootstrap4');
$arNames = $arResult['names'];
global $USER;
Extension::load('vigr.usercard');
\CJSCore::Init(array('vigr.usercard'));

Asset::getInstance()->addCss('https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.0/slimselect.min.css');
?>

<? if ($arResult['logData']): ?>
    <input type="hidden" id='logValue' value='<?= json_encode($arResult['logData']) ?>'>
<? endif ?>

<div>
    <span>Для пользователя</span>
    <select class = 'user'>
        <?foreach ($arResult['user'] as $key=>$user):?>
            <option <?if($key==$USER->getId()):?>selected<?endif?> value = '<?=$key?>'><?=$user?></option>
        <?endforeach;?>
    </select>
</div>

<input type="hidden" id='mainValue' value='<?= json_encode($arResult['mainData']) ?>'>
<input id="mainFieldUl" name="<?= $arParams['userField']['FIELD_ID'] ?>">
<div>
    <div class = 'body'>


<?include 'ur.php'?>
</div>


