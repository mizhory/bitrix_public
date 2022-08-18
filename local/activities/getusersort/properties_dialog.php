<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Код поля</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'code',
            $arCurrentValues['code'])
        ?>
    </td>
    </td>
</tr>

<tr>
    <td align="right" width="40%"><span class="adm-required-field">Элемент ИД</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'IDDDD',
            $arCurrentValues['IDDDD'])
        ?>
    </td>
    </td>
</tr>