<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Список пользователей:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'inputUsersArray',
            $arCurrentValues['inputUsersArray'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Пользователь, которого нужно заменить:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'userFind',
            $arCurrentValues['userFind'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Пользователь, на которого нужно заменить:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'userReplace',
            $arCurrentValues['userReplace'])
        ?>
    </td>
    </td>
</tr>