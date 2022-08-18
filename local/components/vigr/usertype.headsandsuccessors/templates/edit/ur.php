<?php
//ec($arNames);
?>
<span>Выберите компанию</span>
<select class = 'ur' multiple>
    <?foreach ($arNames as $key=>$name):?>
        <option selected value="<?=$key?>"><?=$name?></option>
    <?endforeach;?>
</select>
</div>

<? foreach ($arNames as $id => $name): ?>
<table data-id = "<?=$id?>" class="urL table table-bordered">
    <tbody class = 'body'>
    <tr>
        <td>
            <?= $name ?>
        </td>
        <td>
            <input type="button" value="Удалить" class="delete">
        </td>
    </tr>
    <tr>
        <td>
            Непосредственный руководитель
        </td>
        <td>
            <select data-selector = '.user_h_<?=$id?>' class = 'additional user_h_<?=$id?>'>
                <?foreach ($arResult['user'] as $key=>$user):?>
                    <option <?if($key==$arResult['logData']['id']):?>selected<?endif?> value = '<?=$key?>'><?=$user?></option>
                <?endforeach;?>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            Преемник
        </td>
        <td>
            <select data-selector = '.user_p_<?=$id?>' class = 'additional user_p_<?=$id?>'>
                <?foreach ($arResult['user'] as $key=>$user):?>
                    <option <?if($key==$arResult['logData']):?>selected<?endif?> value = '<?=$key?>'><?=$user?></option>
                <?endforeach;?>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            Дата увольнения
        </td>
        <td>
            <input id = 'date_<?=$id?>' class="date" type = 'date'>
        </td>
    </tr>
    </tbody>
</table>
<? endforeach; ?>
