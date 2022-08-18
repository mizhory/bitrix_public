<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Шаблон строки:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'stringTemplate',
            $arCurrentValues['stringTemplate'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Переменные (разделитель "; "):</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'variables',
            $arCurrentValues['variables'])
        ?>
    </td>
    </td>
</tr>