<div style="width: 100%" class="de-inline-bl be-product-block">
      <span  class="field-wrap fields boolean" <?if(empty($arProducts)):?> style = 'display: none' <?endif;?>>
        <span class="field-item fields boolean">
        <label>
        <input  <?if($arItem['distr'] == 1):?>checked = 'checked'<?endif?> type="checkbox" class = 'distribution' value="1" id="distribution">
        Распределить по всем в равных %
        </label>
        </span>
    </span>

    <div class="de-inline-bl free-sum be-line">
        <div class="ui-entity-editor-block-title de-block de-label">
            <label class="ui-entity-editor-block-title-text">Остаток суммы для продуктов</label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-xs ui-ctl-block">
            <input value="0" type="text" class="ostP ui-ctl-element" disabled>
        </div>
    </div>

    <div class="de-inline-bl free-sum be-line">
        <div class="ui-entity-editor-block-title de-block de-label">
            <label class="ui-entity-editor-block-title-text">Остаток процента для продуктов</label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-xs ui-ctl-block">
            <input value="0" type="text" class="ostPer ui-ctl-element" disabled>
        </div>
    </div>

    <input class = 'countItems' type = 'hidden' value = '6'>
    <?$rand = rand()?>
    <input type = 'hidden' class = 'rand' value = '<?=$rand?>'>
    <span class="product-selection field-wrap fields boolean">
        <span class="field-item fields boolean be-product-type">
            <input  class="fields boolean" type="hidden" value="0" name="distribution">
                <label>
                    <input type="radio" checked  class = 'switchProduct' value="noP" name="switcher_<?=$rand?>">
                    Без продуктов
                </label>
            <?if(!empty($arProducts)):?>
                <label>
                    <input <?if($arItem['mode'] == 'userP'):?> checked='checked' <?endif?> type="radio"  class = 'switchProduct' value="userP" name="switcher_<?=$rand?>">
                    Пользовательский набор
                </label>
                <label>
                    <input <?if($arItem['mode'] == 'allP'):?> checked='checked' <?endif?> type="radio" class = 'switchProduct' value="allP" name="switcher_<?=$rand?>">
                    Все продукты
                </label>
            <?endif?>
        </span>
    </span>

</div>

