<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Context;


global $USER;

$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
'FILTER_ID' => $arResult['filterId'],
'GRID_ID' => $arResult['filterId'],
'FILTER' => $arResult['filterFields'],
'ENABLE_LIVE_SEARCH' => false,
'ENABLE_LABEL' => true
]);

$filter = [];
$filterOption = new Bitrix\Main\UI\Filter\Options($arResult['filterId']);
$filterData = $filterOption->getFilter([]);
foreach ($filterData as $k => $v) {
$filter[$k] = $v;
}

$request = Context::getCurrent()->getRequest();
?>

<table class="table table-bordered">
    <?
    if($request->isAjaxRequest()){
        global $APPLICATION;
        $APPLICATION->restartBuffer();
        ob_start();
    }
    ?>
    <thead>
    <tr>
        <th scope="col">Тип заявки / действия</th>
        <th scope="col">Действие</th>
        <th scope="col">Пользователь</th>
        <th scope="col">Сообщение</th>
        <th scope="col">Дата / Время</th>
    </tr>
    </thead>
    <tbody>
    <?foreach ($arResult['data'] as $arData):?>
        <tr>
            <td class=""><?=$arData['DEAL_TYPE']?></td>
            <td class=""><?=$arData['action']?></td>
            <td class=""><?=$arData['user']?></td>
            <td class=""><?=$arData['message']?></td>
            <td class=""><?=$arData['date']?></td>
        </tr>
    <?endforeach;?>
    </tbody>
    <?
    if($request->isAjaxRequest()){
        echo json_encode([
            'content'=>ob_get_clean()
        ]);
        die();
    }
    ?>
</table>


<script>

    window.addEventListener('message', function(event) {
        var message = event.data;
        if(message === 'close'){
            BX.SidePanel.Instance.close();
        }
    });

    BX.addCustomEvent("SidePanel.Slider:onCloseComplete", function(event) {
        $.post(window.location.href, {'ajax':'y'},function (data) {
            $('.table').html(JSON.parse(data).content);
        })
    });

    BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function (command, params) {
        $.post(window.location.href, {'ajax':'y'},function (data) {
            $('.table').html(JSON.parse(data).content);
        })
    }));

</script>
