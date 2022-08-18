<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->addExternalJS($templateFolder . "/js/wrap.js");
$this->addExternalJS($templateFolder . "/js/be.js");
$this->addExternalJS($templateFolder . "/js/beProduct.js");

$this->addExternalJS($templateFolder . "/js/script_component.js");

if (\Bitrix\Main\Loader::includeModule('currency')) {
    \Bitrix\Main\UI\Extension::load(['currency', 'currency.money-editor']);
}

$arr = [
    'Январь',
    'Февраль',
    'Март',
    'Апрель',
    'Май',
    'Июнь',
    'Июль',
    'Август',
    'Сентябрь',
    'Октябрь',
    'Ноябрь',
    'Декабрь'
];

$month = date('n') - 1;

/**

 * @var $arResult
 */

?>

<div id="be-form-wrapp">

    <input class='countItems' type = 'hidden' value = '5'>

    <input type = 'hidden' id = 'nowMonth' value = '<?=$arr[$month]?>'>
    <input type = 'hidden' id = 'nowYear' value = '<?=date('Y')?>'>

    <input value="<?=$arResult['DRAFT']?>" type = 'hidden' id = 'draftI'>
    <input value="<?=$arResult['ARTICLE']?>" type = 'hidden' id = 'articleI'>
    <input value="<?=$arResult['YEAR']?>" type = 'hidden' id = 'yearI'>
    <input value="<?=$arResult['MONTH']?>" type = 'hidden' id = 'monthI'>

    <div class="be-form" id="be-choose-be-form">
        <div class="be-item" id="be_sum">
            <div>
                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block main-input-value">
                    <input type="hidden" name="<?=$arParams['HTML_CONTROL']['VALUE']?>" id="widget_data" class="ui-ctl-element mainSum">
                </div>
                <div class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Страна БЕ плательщика *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select id ='country' class="ui-ctl-element">
                            <!--<option value="N">не выбрана</option>-->
                            <?php
                            foreach($arResult['COUNTRY'] as $id => $arCountry):
                                $selected = "";
                                if($id == $arResult['COUNTRY_NOW'] or (empty($arResult['COUNTRY_NOW']) and $arCountry['DEFAULT'] == 'Y')){
                                    $selected = 'selected';
                                }
                                ?>
                                <option <?=$selected?> data-rate="<?=$arCountry['RATE_ID']?>" value="<?=$id?>" <?=$selected?>><?=$arCountry['NAME']?></option>
                            <?
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div style="display: none" class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Валюта БЕ *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select  id ='rateBe' class="ui-ctl-element">
                            <!--<option value="N">не выбрана</option>-->
                            <?php
                            foreach($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                                $selected = "";
                                if($elemCurrency['CODE'] === $arResult['RATE_BE']){
                                    $selected = 'selected';
                                }
                                ?>
                                <option <?=$selected?> data-rate="<?=$elemCurrency['CODE']?>" value="<?=$elemCurrency['ID']?>" <?=$selected?>>
                                    <?=$elemCurrency['NAME']?>
                                </option>
                            <?
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Статья расходов *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select id ='article' class="ui-ctl-element" data-empty-value="N">
                            <option value="N">Не выбрано</option>
                            <?php
                            foreach($arResult['ARTICLES'] as $id => $elemCurrency):
                                $selected = "";
                                if($id == $arResult['ARTICLE']){
                                    $selected = 'selected';
                                }
                                ?>
                                <option <?=$selected?> value="<?=$id?>" <?=$selected?>><?=$elemCurrency?></option>
                            <?
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Валюта заявки *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select  id ='rate' class="ui-ctl-element">
                            <!--<option value="N">не выбрана</option>-->
                            <?php
                            foreach($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                                $selected = "";
                                if($elemCurrency['CODE'] === $arResult['RATE']){
                                    $selected = 'selected';
                                }
                                ?>
                                <option <?=$selected?> data-rate="<?=$elemCurrency['CODE']?>" value="<?=$elemCurrency['ID']?>" <?=$selected?>>
                                    <?=$elemCurrency['NAME']?>
                                </option>
                            <?
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div class="de-inline-bl de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">Сумма *:</label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block"> <!-- 1. Основной контейнер -->
                        <input value = "<?=$arResult['MAIN_SUM'] ?? 0?>" type="text" class="ui-ctl-element sumInput">  <!-- 2. Основное поле -->
                    </div>
                </div>

                <div class="de-inline-bl free-sum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">Свободный остаток *: </label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <input value = '<?=$arResult['FREE_SUM'] ?? 0?>' disabled id = 'free_sum' type="text" class="ui-ctl-element">
                    </div>
                </div>

                <div class="de-inline-bl free-sum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">Остаток (%) *: </label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <input value = '<?=$arResult['FREE_PERCENT'] ?? 100?>' disabled id = 'free_percent' type="text" class="ui-ctl-element">
                    </div>
                </div>

                <div class="de-inline-bl free-sum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">Курс валюты плательщика относительно курсов БЕ *: </label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <input value = "<?=$arResult['RATE_CURSE'] ?? '1'?>" id= 'rateCurse' type="text" class="ui-ctl-element">
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px">
                <div>
                    Примечание:
                    Выбрать все БЕ можно только в 1 валюте
                    <br>
                    Внимание ! Неккоректно работает в браузерах safari - mozilla
                </div>
                <br>
                <br>
                Ошибки формы :
                <div style="color:green;display: none" class = 'success'>
                    Все атлична!
                </div>
                <div style="color: red" class = 'errors'>

                </div>
            </div>
        </div>
        <?//ec('123',1,1)?>
        <span class="field-wrap fields boolean">
        <span class="field-item fields boolean">
            <label style="width: 230px">
                <input type="checkbox" id = 'distribution' value="1" <?if($arResult['DISTR'] == 1):?>checked = 'checked'<?endif?>>
                Распределить по всем в равных %
            </label>
        </span>
    </span>

        <?if (empty($arResult['ITEMS'])):?>
            <?
            $beId = 0;
            $disabledDelete = 'disabled';
            $arItem = [];
            ?>
            <?include 'be.php'?>
        <?else:?>
            <?foreach ($arResult['ITEMS'] as $arItem):?>
                <?$beId = $arItem['id']?>
                <?include 'be.php'?>
            <?endforeach;?>
        <?endif;?>

        <div class = 'cloneBlocks wrap be' style="display: none">
            <?$arItem = [];?>
            <?$beId = -1?>
            <?include 'be.php'?>
            <?include 'beProduct.php'?>
        </div>
    </div>
</div>

<style>
    .error-option{
        background: grey;
        opacity: 50%;
    }
</style>
<script type="text/javascript">
    BX(function () {
        window.bizUnitClass2 = new WrapItemsIblock({
            wrap: document.querySelector("#be-form-wrapp"),
            mainInputSelector: 'input[name="<?=$arParams['HTML_CONTROL']['VALUE']?>"]',
            props: <?=\CUtil::PhpToJSObject($arParams['PROPERTIES'] ?? [])?>,
            draftId: '<?=$arParams['DRAFT_ID']?>',
            cursRate: '<?=$arResult['RATE_CURSE']?>',
            formSelector: '<?=(empty($arParams['PROPERTY']['ELEMENT_ID']))
                ? "form_lists_element_add_{$arParams['PROPERTY']['IBLOCK_ID']}"
                : "form_lists_element_edit_{$arParams['PROPERTY']['IBLOCK_ID']}"?>'
        });
    });
</script>


















