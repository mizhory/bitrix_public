<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $arCurrentValues
 */
?>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ID элемента:</span></td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'int',
            'ELEMENT_ID',
            $arCurrentValues['ELEMENT_ID'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">ID информационного блока:</span></td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'int',
            'IBLOCK_ID',
            $arCurrentValues['IBLOCK_ID'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Действие:</span></td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'text',
            'ACTION_TITLE',
            $arCurrentValues['ACTION_TITLE'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="adm-required-field">Пользователь:</span></td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'user',
            'USER_ID',
            $arCurrentValues['USER_ID'],
            ['rows' => '2']
        )
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%">ID БП:</td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'text',
            'WORKFLOW_ID',
            $arCurrentValues['WORKFLOW_ID'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span>Состояние:</span></td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'text',
            'STAGE_TITLE',
            $arCurrentValues['STAGE_TITLE'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="">Комментарий:</span></td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'text',
            'COMMENT',
            $arCurrentValues['COMMENT'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%"><span class="">Дата создания:</span></td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'datetime',
            'DATE_CREATE',
            $arCurrentValues['DATE_CREATE'])
        ?>
    </td>
</tr>
