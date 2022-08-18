<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Ид заявки:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'dealId',
            $arCurrentValues['dealId'])
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
            'user',
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
            'user',
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
            'user',
            'sum',
            $arCurrentValues['sum'])
        ?>
    </td>
</tr>