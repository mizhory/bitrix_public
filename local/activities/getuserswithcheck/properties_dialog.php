<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
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
    <td align="right" width="40%"><span class="adm-required-field">Юр лицо(подразделение):</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'depId',
            $arCurrentValues['depId'])
        ?>
    </td>
    </td>
</tr>

<tr>
    <td align="right" width="40%"><span class="adm-required-field">Отдавать в виде числа:</span>
    </td>
    <td width="60%">
        <input type="checkbox" <?if($arCurrentValues['chislo'] != 'N'):?> checked="checked"<?endif?> name = 'chislo' value="1">
    </td>
    </td>
</tr>