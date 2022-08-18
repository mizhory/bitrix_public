<?

if($i < 1){
    include 'beProductHeader.php';
}
?>
<div class="beproduct-item new" data-id = '<?=$productItem['id'] ?? 0?>'>
    <div class="de-inline-bl de-bi " >
        <div class="ui-entity-editor-block-title de-block de-label">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">
            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <select disabled class="ui-ctl-element select-item" >
                <option value = '0'>Не выбрано</option>
                <?foreach ($arProducts as $key=>$product):?>
                    <?
                    $selected = '';
                    if($productItem['id'] == $key){
                        $selected = 'selected';
                    }
                    ?>
                    <option <?=$selected?> value = '<?=$key?>'><?=$product?></option>
                <?endforeach?>
            </select>
        </div>
    </div>

    <div class="de-inline-bl de-psnt">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input value = "<?=$productItem['percent'] ?? 0?>" type="text" class="percentInput ui-ctl-element">
        </div>

    </div>

    <div class="de-inline-bl de-psnt" style="display: none">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input value = "0" type="text" class="budgetInput ui-ctl-element">
        </div>

    </div>

    <div class="de-inline-bl de-sum">

        <div class="ui-entity-editor-block-title de-block">
            <label class="ui-entity-editor-block-title-text"></label>
        </div>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
            <input value = "<?=$productItem['sum'] ?? 0?>"  type="text" class="sumInput ui-ctl-element" placeholder="сумма" >
        </div>
    </div>

    <div class="de-inline-bl ">
        <button type="button" class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm">Еще</button>
    </div>

    <div class="de-inline-bl">
        <button type="button" class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X</button>
    </div>

</div>


