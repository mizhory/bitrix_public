<?php
?>

<tr>
    <td>
        Юр лицо
    </td>
    <td>
        Непосредственный руководитель
    </td>
    <td>
        Исполняющий обязанности
    </td>
</tr>
<?//$arResult['logData']['id'] = 13?>
<?$i=1;?>
<? foreach ($arNames as $id => $name): ?>
<?$i++;?>
    <tr class='urL' data-id='<?= $id ?>'>
        <input type="hidden" class='uId' value="<?=$id?>">
        <td>
            <?= $name ?>
        </td>
        <td>
            <select data-selector = '.user_h_<?=$id?>' class = 'additional user_h_<?=$id?>'>
                <?foreach ($arResult['user'] as $key=>$user):?>
                    <option <?if($key==$arResult['logData']['id']):?>selected<?endif?> value = '<?=$key?>'><?=$user?></option>
                <?endforeach;?>
            </select>
        </td>
        <?$i++;?>
        <td>
            <select data-selector = '.user_i_<?=$id?>' class = 'additional user_i_<?=$id?>'>
                <option value = '0'>Не выбрано</option>
                <?foreach ($arResult['user'] as $key=>$user):?>
                    <option  value = '<?=$key?>'><?=$user?></option>
                <?endforeach;?>
            </select>
        </td>
    </tr>
<? endforeach; ?>
