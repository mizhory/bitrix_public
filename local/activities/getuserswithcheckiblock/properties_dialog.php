<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Пользователи:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'user',
            $arCurrentValues['user'])
        ?>
    </td>
    </td>
</tr>
<tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Согласовавшие пользователи:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'doneUsers',
            $arCurrentValues['doneUsers'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИД БЕ пользователя:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'beId',
            $arCurrentValues['beId'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИД БЕ пользователя (по старому списку):</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'beIdOld',
            $arCurrentValues['beIdOld'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИД юрлица пользователя:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'depId',
            $arCurrentValues['depId'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИД юрлица пользователя (по старому списку):</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'depIdOld',
            $arCurrentValues['depIdOld'])
        ?>
    </td>
    </td>
</tr>