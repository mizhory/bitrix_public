<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИБ:</span>
    </td>
    <td width="60%">
        <select name = 'bp'>
            <?foreach ($arCurrentValues['IBLOCKS'] as $key=>$iblock):?>
                <option <?if($key == $arCurrentValues['IB']):?>selected<?endif;?> value="<?=$key?>">
                    <?=$iblock?>
                </option>
            <?endforeach;?>s
        </select>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Стадия:</span>
    </td>
    <td width="60%">
        <input name="stage" type="text" value="<?=$arCurrentValues['stage']?>">
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИД подразделения пользователя:</span>
    </td>
    <td width="60%">
        <input value="<?=$arCurrentValues['depId']?>" name="depId" type="text">
    </td>
    </td>
</tr>