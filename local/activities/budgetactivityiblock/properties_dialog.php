<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Ид заявки:</span>
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
    <td align="right" width="40%"><span class="adm-required-field">Ид инфоблока заявки:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'iblockId',
            $arCurrentValues['iblockId'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Курс:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'curse',
            $arCurrentValues['curse'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Перерерзирование:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'reReserve',
            $arCurrentValues['reReserve'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Действие (CLOSED,PAY,PARTIAL_PAY,CANCEL):</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'action',
            $arCurrentValues['action'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Сумма (только при PARTIAL_PAY!):</span>
    </td>
    <td width="60%">
         <?= CBPDocument::ShowParameterField(
            '',
            'sum',
            $arCurrentValues['sum'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Тип заявки (безналичный, наличный)</span>
    </td>
    <td width="60%">
         <?= CBPDocument::ShowParameterField(
            '',
            'adjective',
            $arCurrentValues['adjective'])
        ?>
    </td>
</tr>