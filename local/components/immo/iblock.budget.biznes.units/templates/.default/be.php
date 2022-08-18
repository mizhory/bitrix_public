<?
/**

 *@var int $beId
 *@var string $disabledDelete
 * @var array $arResult
 * @var array $arItem
 */

?>

<div class="be-item new" data-id = <?=$beId?>>

    <div class="be-line-item">
        <div class="de-inline-bl de-bi">
            <div class="ui-entity-editor-block-title de-block de-label">
                <label class="ui-entity-editor-block-title-text">Бизнес единица *:</label>
            </div>

            <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                <select class="ui-ctl-element select-item">
                    <option data-rate='' value="0">Не выбрано</option>
                    <?foreach ($arResult['ALL_BE'] as $idBe => $nameBe):?>
                        <?
                        $country = $arResult['BE_VS_COUNTRY'][$idBe];
                        $rateId = $arResult['COUNTRY'][$country]['RATE_ID'];
                        $rateCode = $arResult['CURRENCY_LIST'][$rateId]['CODE'];

                        $selected = '';
                        if($arItem['id'] == $idBe){
                            $selected = 'selected';
                        }
                        ?>
                        <option data-rate="<?=$rateCode?>" <?=$selected?> value="<?=$idBe?>"><?=$nameBe?> - <?=$arResult['CURRENCY_LIST'][$rateId]['CODE']?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>

        <div class="de-inline-bl de-psnt">

            <div class="ui-entity-editor-block-title de-block">
                <label class="ui-entity-editor-block-title-text">% затрат *:</label>
            </div>

            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                <input value = "<?=$arItem['percent'] ?? 0?>" type="text" class="percentInput ui-ctl-element">
            </div>

        </div>

        <div class="de-inline-bl de-sum">

            <div class="ui-entity-editor-block-title de-block">
                <label class="ui-entity-editor-block-title-text">Сумма *:</label>
            </div>

            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                <input value = "<?=$arItem['sum'] ?? 0?>" type="text" class="sumInput ui-ctl-element" placeholder="сумма" >
            </div>
        </div>

        <div class="de-inline-bl de-sum" style="display: none">
            <div class="ui-entity-editor-block-title de-block">
                <label class="ui-entity-editor-block-title-text">Бюджет</label>
            </div>

            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                <input value="<?=$arResult['AR_BUDGETS'][$arItem['id']]?>" disabled type="text" class="budgetInput ui-ctl-element" placeholder="сумма">
            </div>
        </div>

        <div class="de-inline-bl ">
            <button type="button" class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm" >Еще</button>
        </div>

        <div class="de-inline-bl">
            <button type="button" <?=$disabledDelete?> class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X</button>
        </div>
    </div>

    <div style='display:none' class='warning'>Не хватает бюджета</div>
    <?$arProducts = []?>
    <div class='product-section'>
        <?if($arItem['items']):?>
            <?$i = 0;?>
            <?foreach ($arItem['items'] as $productItem):?>
                <?$arProducts = $arResult['ALL_BE_PRODUCTS'][$beId]?>
                <?include 'beProduct.php'?>
                <?$i++;?>
            <?endforeach;?>
        <?else:?>
            <?include 'beProductHeader.php'?>
            <input class = 'countItems' type = 'hidden' value = '6'>
        <?endif?>
    </div>
</div>

<script>

</script>