<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ID Заявки:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'idPrint',
            $arCurrentValues['idPrint'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ID Инфоблока:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'iblockIdPrint',
            $arCurrentValues['iblockIdPrint'])
        ?>
    </td>
</tr>
