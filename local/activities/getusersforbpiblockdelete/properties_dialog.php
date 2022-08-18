<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИД заявки (инфоблок):</span>
    </td>
    <td width="60%">
         <?= CBPDocument::ShowParameterField(
            '',
            'dealId',
            $arCurrentValues['dealId'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ИД БЕ:</span>
    </td>
    <td width="60%">
         <?= CBPDocument::ShowParameterField(
            '',
            'beId',
            $arCurrentValues['beId'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Код свойства, по которому осуществляется поиск:</span>
    </td>
    <td width="60%">
         <?= CBPDocument::ShowParameterField(
            '',
            'propCode',
            $arCurrentValues['propCode'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Код свойства, по которому привязана сущность:</span>
    </td>
    <td width="60%">
         <?= CBPDocument::ShowParameterField(
            '',
            'entityCode',
            $arCurrentValues['entityCode'])
        ?>
    </td>
</tr>