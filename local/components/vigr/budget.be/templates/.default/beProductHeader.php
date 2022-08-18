<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}?>
<div style="width: 100%" class="de-inline-bl">
      <span  class="field-wrap fields boolean" <?if(empty($arProducts)):?> style = 'display: none' <?endif;?>>
        <span class="field-item fields boolean">
                <label style="width: 230px">
                    <input  <?if($arItem['distr'] == 1):?>checked = 'checked'<?endif?> type="checkbox" class = 'distribution' value="1" id="distribution">
                    Распределить по всем в равных %
                </label>
        </span>
    </span>
    <label style="width: 163px;display: none">
        Остаток суммы для продуктов
        <input disabled type="text" class="ostP" value="0">
    </label>
    <br>
    <label style="width: 163px;display: none">
        Остаток процента для продуктов
        <input disabled type="text" class="ostPer" value="0">
    </label>
    <input class = 'countItems' type = 'hidden' value = '6'>
        <?$rand = rand()?>
        <input type = 'hidden' class = 'rand' value = '<?=$rand?>'>
        <span style = 'display: {displaySwitch}' class="field-wrap fields boolean">
            <span class="field-item fields boolean">
                <input  class="fields boolean" type="hidden" value="0" name="distribution">
                    <label style="width: 95px">
                        <input type="radio" checked  class = 'switchProduct' value="noP" name="switcher_<?=$rand?>">
                        Без продуктов
                    </label>
                <?if(!empty($arProducts)):?>
                    <label style="width: 163px">
                        <input <?if($arItem['mode'] == 'userP'):?> checked='checked' <?endif?> type="radio"  class = 'switchProduct' value="userP" name="switcher_<?=$rand?>">
                        Пользовательский набор
                    </label>
                    <label style="width: 90px">
                        <input <?if($arItem['mode'] == 'allP'):?> checked='checked' <?endif?> type="radio" class = 'switchProduct' value="allP" name="switcher_<?=$rand?>">
                        Все продукты
                    </label>
                <?endif?>
            </span>
        </span>

</div>

