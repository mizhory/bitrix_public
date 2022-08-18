<?php
echo 1234;
?>
<table style="text-align: center" class="table table-bordered">
<tr>
    <td>
        Юр лицо
    </td>
    <td>
        Непосредственный руководитель
    </td>
    <td>
        ИО
    </td>
</tr>
<thead>
<th colspan="3">Для пользователя <?=$arResult['DATA']['USERS'][$arResult['DATA']['MAIN']['data_user']]?></th>
</thead>
<? foreach ($arResult['DATA']['MAIN']['values'] as $arValue): ?>
    <tbody class = 'body'>
    <tr class='urL' data-id='<?= $id ?>'>
        <input type="hidden" class='uId' value="<?=$id?>">
        <td>
            <?=$arResult['DATA']['SECTIONS'][$arValue['id']]?>
        </td>
        <td>
            <?=$arResult['DATA']['USERS'][$arValue['head']]?>
        </td>
        <td>
            <?=$arResult['DATA']['USERS'][$arValue['isp']]?>
        </td>
    </tr>
    <tbody class = 'body'>
<? endforeach; ?>
</table>


