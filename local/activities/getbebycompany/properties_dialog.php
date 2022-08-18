<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ID юр. лица:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'idCompany',
            $arCurrentValues['idCompany'])
        ?>
    </td>
    </td>
</tr>
