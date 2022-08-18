<?php
/**
 * @var array $arCurrentValues
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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
    <td align="right" width="40%">Конец строки:</td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'text',
            'SEPARATOR',
            $arCurrentValues['SEPARATOR']
        )
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%">Строка:</td>
    <td width="60%">
        <?= CBPDocument::ShowParameterField(
            'text',
            'LINE',
            $arCurrentValues['LINE'])
        ?>
    </td>
</tr>
<tr>
    <td align="right" width="40%">#DATE_CREATE# - </td>
    <td width="60%">
       Дата создания
    </td>
</tr>
<tr>
    <td align="right" width="40%">#STAGE_TITLE# - </td>
    <td width="60%">Состояние</td>
</tr>
<tr>
    <td align="right" width="40%">#ACTION_TITLE# - </td>
    <td width="60%">Событие</td>
</tr>
<tr>
    <td align="right" width="40%">#USER_NAME# - </td>
    <td width="60%">Имя пользователя</td>
</tr>
<tr>
    <td align="right" width="40%">#USER_SECOND_NAME# - </td>
    <td width="60%">Отчество пользователя</td>
</tr>
<tr>
    <td align="right" width="40%">#USER_SECOND_NAME# - </td>
    <td width="60%">Отчество пользователя</td>
</tr>
<tr>
    <td align="right" width="40%">#USER_LAST_NAME# - </td>
    <td width="60%">Фамилия пользователя</td>
</tr>
<tr>
    <td align="right" width="40%">#COMMENT# - </td>
    <td width="60%">Комментраий пользователя</td>
</tr>