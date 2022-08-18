<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->addExternalJS('/local/modules/vigr.budget/js/imask.js');
$this->addExternalJS($templateFolder . '/js/wrap.js');
$this->addExternalJS($templateFolder . '/js/be.js');
$this->addExternalJS($templateFolder . '/js/beProduct.js');

if (\Bitrix\Main\Loader::includeModule('currency')) {
    \Bitrix\Main\UI\Extension::load(['currency', 'currency.money-editor', 'crm.entity-editor']);
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

$defCountry = '';
if (!empty($arResult['COUNTRY'])) {
    foreach ($arResult['COUNTRY'] as $id => $country) {
        if ($country['DEFAULT'] != 'Y') {
            continue;
        }

        $defCountry = $id;
        break;
    }
}

/**
 * @var $arResult
 */

?>

<div id="be-form-wrapp">

    <input class='countItems' type='hidden' value='5'>

    <input type='hidden' id='nowMonth' value='<?= $arr[$month] ?>'>
    <input type = 'hidden' id = 'nowYear' value = '<?=\Immo\Iblock\Property\BiznesUnitsIblockField::defineFinancialYear()?>'>

    <input value="<?= $arResult['DRAFT'] ?>" type='hidden' id='draftI'>
    <input value="<?= $arResult['FR'] ?>" type='hidden' id='FD'>
    <input value="<?= $arResult['ARTICLE'] ?>" type='hidden' id='articleI'>
    <input value="<?= $arResult['YEAR'] ?>" type='hidden' id='yearI'>
    <input value="<?= $arResult['MONTH'] ?>" type='hidden' id='monthI'>

    <div class="be-form" id="be-choose-be-form">
        <div class="be-item" id="be_sum">
            <div>
                <input type="hidden" name="<?=$arParams['HTML_CONTROL']['VALUE']?>" id="widget_data" class="ui-ctl-element mainSum">

                <?if ($arParams['USE_BANK'] and !empty($arResult['BANK']['elementBank'])):?>
                    <div style="margin-bottom: 25px" class="de-allsum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">
                                Плательщик *</label>
                        </div>

                        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                            <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                            <select id='bank' class="ui-ctl-element">
                                <option value="0" data-countryId="0">Не выбрано</option>
                                <?php
                                foreach ($arResult['BANK']['elementBank'] as $id => $bank):
                                    if (empty($arResult['BE_VS_COUNTRY'][$id])) {
                                        continue;
                                    }

                                    if ($bank['ACTIVE'] != 'Y' and $id != $arResult['BANK_BE']) {
                                        continue;
                                    }
                                    ?>
                                    <option <?= ($id == $arResult['BANK_BE']) ? 'selected' : '' ?>
                                            data-countryId="<?= $arResult['BE_VS_COUNTRY'][$id] ?>"
                                            value="<?= $id ?>"><?= $bank['NAME'] ?>
                                    </option>
                                <?endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?endif;?>
                <?if ($arParams['USE_COMPANY']):?>
                    <div style="margin-bottom: 25px" class="de-allsum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">
                                Плательщик (Юр. Лицо) (список юр. лиц) *</label>
                        </div>

                        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                            <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                            <select id='urList' class="ui-ctl-element">
                                <option value="0" data-countryId="<?=$defCountry?>">Не выбрано</option>
                                <?php
                                foreach ($arResult['UR_LISTS'] as $id => $elemCurrency):
                                    $selected = "";
                                    if ($id == $arResult['UR']) {
                                        $selected = 'selected';
                                    }

                                    if ($elemCurrency['ACTIVE'] != 'Y' and $id != $arResult['UR']) {
                                        continue;
                                    }

                                    ?>
                                    <option <?= $selected ?>
                                            data-countryId="<?= $arResult['BE_VS_COUNTRY'][$arResult['UR_LISTS_VS_BE'][$id]] ?>"
                                            value="<?= $id ?>"><?= $elemCurrency['NAME'] ?></option>
                                <?
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                <?endif;?>

                <div style="margin-bottom: 25px" class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Страна БЕ плательщика *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select id='country' <?=($arParams['USE_BANK']) ? 'disabled' : ''?> class="ui-ctl-element">
                            <?php
                            foreach ($arResult['COUNTRY'] as $id => $arCountry):
                                $selected = "";
                                if ($id == $arResult['COUNTRY_NOW'] or (empty($arResult['COUNTRY_NOW']) and $arCountry['DEFAULT'] == 'Y')) {
                                    $selected = 'selected';
                                }

                                if ($arCountry['ACTIVE'] != 'Y' and $id != $arResult['COUNTRY_NOW']) {
                                    continue;
                                }

                                ?>
                                <option <?= $selected ?> data-rate="<?= $arCountry['RATE_ID'] ?>"
                                                         value="<?= $id ?>" <?= $selected ?>><?= $arCountry['NAME'] ?></option>
                            <?
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 25px" class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Статья расходов *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select id='article' class="ui-ctl-element">
                            <option value="0">Не выбрано</option>
                            <?php

                            foreach ($arResult['ARTICLES'] as $id => $article):
                                if ($article['ACTIVE'] != 'Y' and $id != $arResult['ARTICLE']) {
                                    continue;
                                }

                                $selected = "";
                                if ($id == $arResult['ARTICLE']) {
                                    $selected = 'selected';
                                }
                                ?>
                                <option <?= $selected ?> value="<?= $id ?>" <?= $selected ?>><?= $article['NAME'] ?></option>
                            <?
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 25px; display: none" class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Валюта БЕ *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select id='rateBe' class="ui-ctl-element">
                            <?php
                            foreach ($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                                $selected = "";
                                if ($elemCurrency['CODE'] === $arResult['RATE_BE']) {
                                    $selected = 'selected';
                                }
                                ?>
                                <option <?= $selected ?> data-rate="<?= $elemCurrency['CODE'] ?>"
                                                         value="<?= $elemCurrency['ID'] ?>" <?= $selected ?>>
                                    <?= $elemCurrency['NAME'] ?>
                                </option>
                            <?
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 25px" class="de-allsum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Валюта заявки *:
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                        <select id='rate' <?=($arParams['USE_BANK']) ? 'disabled' : ''?> class="ui-ctl-element">
                            <?php
                            foreach ($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                                $selected = "";
                                if ($elemCurrency['CODE'] === $arResult['RATE']) {
                                    $selected = 'selected';
                                }

                                if ($elemCurrency['ACTIVE'] != 'Y' and $elemCurrency['CODE'] != $arResult['RATE']) {
                                    continue;
                                }

                                ?>
                                <option <?= $selected ?> data-rate="<?= $elemCurrency['CODE'] ?>"
                                                         value="<?= $elemCurrency['ID'] ?>" <?= $selected ?>>
                                    <?= $elemCurrency['NAME'] ?>
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

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <input value="<?= $arResult['MAIN_SUM'] ?? 0 ?>" type="text" class="ui-ctl-element sumInput">
                    </div>
                </div>

                <div class="de-inline-bl free-sum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">Свободный остаток</label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <input value='<?= $arResult['FREE_SUM'] ?? 0 ?>' disabled id='free_sum' type="text"
                               class="ui-ctl-element">
                    </div>
                </div>

                <div class="de-inline-bl free-sum be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">Остаток (%)</label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <input value='<?= $arResult['FREE_PERCENT'] ?? 100 ?>' disabled id='free_percent' type="text"
                               class="ui-ctl-element">
                    </div>
                </div>

                <div class="de-inline-bl free-sum be-line" <?=$arParams['USE_BANK'] ? 'style="display: none;"' : ''?>>
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">
                            Курс валюты плательщика относительно курсов БЕ *:
                            <span <?=(empty($arResult['RATE_CURRENCY_RATE']) ? 'style="display: none"' : '')?>>
                                <br>
                                Дата обновления курса: <span id="date-currency-rate"><?=$arResult['RATE_CURRENCY_RATE']?></span>
                            </span>
                        </label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <input value="<?= $arResult['RATE_CURSE'] ?? '1' ?>" id='rateCurse' type="text"
                               class="ui-ctl-element">
                    </div>
                </div>

                <div class="de-inline-bl be-line">
                    <div class="ui-entity-editor-block-title de-block de-label">
                        <label class="ui-entity-editor-block-title-text">Обновить бюджет: </label>
                    </div>

                    <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                        <button type="button" class="updateBudget de-inline-bl ui-btn ui-btn-success ui-ctl-block ui-btn-sm">Обновить</button>
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px">
                <div>
                    Примечание:
                    Выбрать все БЕ можно только в 1 валюте
                    <br>
                </div>
                <br>
                <br>
                Ошибки формы :
                <div style="color:green;display: none" class='success'>
                    Все атлична!
                </div>
                <div style="color: red" class='errors'>

                </div>
            </div>
        </div>
        <span class="field-wrap fields boolean" style="max-width: 600px">
            <span class="field-item fields boolean">
            <label>
                <input type="checkbox" id='distribution' value="1"
                       <? if ($arResult['DISTR'] == 1 or empty($arParams['userField']['VALUE'])): ?>checked='checked'<? endif ?>>
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
            <?
            $i = 1;
            foreach ($arResult['ITEMS'] as $arItem):
                $disabledDelete = ($i == 1) ? 'disabled' : '';
                ++$i;
            ?>
                <?$beId = $arItem['id']?>
                <?include 'be.php'?>
            <?endforeach;?>
        <?endif;?>
        <div class='cloneBlocks wrap be' style="display: none">
            <? $arItem = []; ?>
            <? $beId = -1 ?>
            <? $forClone = true ?>
            <div class="be-item new" data-id= <?= $beId ?>>

                <div class="be-line-item">
                    <div class="de-inline-bl de-bi <?=($arParams['MULTIPLE_BE'] == 'Y') ? '' : 'full-size'?>">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Бизнес единица *:</label>
                        </div>

                        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                            <select class="ui-ctl-element select-item">
                                <option data-rate='' value="0">Не выбрано</option>
                                <? foreach ($arResult['ALL_BE'] as $idBe => $arBe): ?>
                                    <?
                                    $country = $arResult['BE_VS_COUNTRY'][$idBe];
                                    $rateId = $arResult['COUNTRY'][$country]['RATE_ID'];
                                    $rateCode = $arResult['CURRENCY_LIST'][$rateId]['CODE'];

                                    $selected = '';
                                    if ($arItem['id'] == $idBe) {
                                        $selected = 'selected';
                                    }
                                    if ($arBe['ACTIVE'] != 'Y' and $arItem['id'] != $idBe) {
                                        continue;
                                    }
                                    ?>
                                    <option data-rate="<?= $rateCode ?>" <?= $selected ?> value="<?= $idBe ?>"><?= $arBe['NAME'] ?>
                                        - <?= $arResult['CURRENCY_LIST'][$rateId]['CODE'] ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?if ($arParams['MULTIPLE_BE'] == 'Y'):?>
                        <div class="de-inline-bl ">
                            <button type="button" class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm">Еще</button>
                        </div>

                        <div class="de-inline-bl">
                            <button type="button" disabled class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X
                            </button>
                        </div>
                    <?endif;?>
                </div>

                <div class="be-line-item">
                    <div class="de-inline-bl de-psnt">

                        <div class="ui-entity-editor-block-title de-block">
                            <label class="ui-entity-editor-block-title-text">% затрат *:</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <input value="0" type="text" class="percentInput ui-ctl-element">
                        </div>

                    </div>

                    <div class="de-inline-bl de-sum">

                        <div class="ui-entity-editor-block-title de-block">
                            <label class="ui-entity-editor-block-title-text">Сумма *:</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <input value="0" type="text" class="sumInput ui-ctl-element" placeholder="сумма">
                        </div>
                    </div>

                    <div class="de-inline-bl de-sum" style="display: none">
                        <div class="ui-entity-editor-block-title de-block">
                            <label class="ui-entity-editor-block-title-text">Бюджет</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <input value="<?=$arResult['AR_BUDGETS'][$arItem['id']] ?>" disabled type="text"
                                   class="budgetInput ui-ctl-element" placeholder="сумма">
                        </div>
                    </div>
                </div>

                <div style='display:none' class='warning'>Не хватает бюджета</div>
                <? $arProducts = [] ?>

                <div class='product-section'>
                    <? include 'beProductHeader.php'; ?>
                </div>
            </div>
            <?$productItem['id'] = 0?>
            <? include 'beProduct.php' ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    BX(function () {
        window.bizUnitClass2 = new WrapItems({
            wrap: document.querySelector("#be-form-wrapp"),
            mainInputSelector: 'input[name="<?=$arParams['HTML_CONTROL']['VALUE']?>"]',
            props: <?=\CUtil::PhpToJSObject($arParams['PROPERTIES'] ?? [])?>,
            draftId: '<?=$arParams['DRAFT_ID']?>',
            cursRate: '<?=$arResult['RATE_CURSE']?>',
            formSelector: '<?=(empty($arParams['PROPERTY']['ELEMENT_ID']))
                ? "form_lists_element_add_{$arParams['PROPERTY']['IBLOCK_ID']}"
                : "form_lists_element_edit_{$arParams['PROPERTY']['IBLOCK_ID']}"?>',
            financialYear: <?=\Immo\Iblock\Property\BiznesUnitsIblockField::defineFinancialYear()?>,
            bankParams: <?=\CUtil::PhpToJSObject($arResult['BANK'] ?? [])?>,
            defCountry: '<?=$defCountry ?? 0?>'
        });
    });
</script>
