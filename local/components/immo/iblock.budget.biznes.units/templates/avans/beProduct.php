<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();



if($i < 1){
    include 'beProductHeader.php';
}
?>
<div class="product-wrap new" data-id = '<?=$productItem['id'] ?? 0?>'>
    <div class="beproduct-item">
        <div class="product">
            <div class="de-inline-bl de-bi " >
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Продукт *:</label>
                </div>

                <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">
                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                    <select  class="ui-ctl-element select-item" >
                        <option value = '0'>Не выбрано</option>
                        <?foreach ($arProducts as $key=>$product):?>
                            <?
                            if ($product['ACTIVE'] != 'Y' and $productItem['id'] != $key) {
                                continue;
                            }

                            $selected = '';
                            if($productItem['id'] == $key && !$empty){
                                $selected = 'selected';
                            }
                            ?>
                            <option <?=$selected?> value = '<?=$key?>'><?=$product['NAME']?></option>
                        <?endforeach?>
                    </select>
                </div>
            </div>

            <div class="de-inline-bl ">
                <button type="button" class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm">Еще</button>
            </div>

            <div class="de-inline-bl">
                <button type="button" class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X</button>
            </div>
        </div>
    </div>

    <div class="beproduct-item">
        <div class="product">
            <div class="de-inline-bl de-psnt">

                <div class="ui-entity-editor-block-title de-block">
                    <label class="ui-entity-editor-block-title-text">% затрат *:</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input  value = "<?=$productItem['percent'] ?? 0?>" type="text" class="percentInput ui-ctl-element">
                </div>

            </div>


            <div class="de-inline-bl de-sum">

                <div class="ui-entity-editor-block-title de-block">
                    <label class="ui-entity-editor-block-title-text">Сумма *:</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input  value = "<?=$productItem['sum'] ?? 0?>"  type="text" class="sumInput ui-ctl-element" placeholder="сумма" >
                </div>
            </div>
        </div>
    </div>

    <div class="beproduct-item">
        <div>
            <div class="de-inline-bl free-sum be-line product-comment">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Комментарий</label>
                </div>
                <div class="ui-ctl ui-ctl-textarea ui-ctl-no-resize">
                    <textarea name="description" class="ui-ctl-element"><?=$productItem['description']?></textarea>
                </div>
            </div>
        </div>
    </div>

</div>


