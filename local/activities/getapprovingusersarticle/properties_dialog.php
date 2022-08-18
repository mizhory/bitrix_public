<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ID статьи расходов:</span>
    </td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            '',
            'articleId',
            $arCurrentValues['articleId'])
        ?>
    </td>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Список согласовавших пользователей:</span>
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
