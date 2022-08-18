<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$this->addExternalJS('/local/modules/vigr.budget/js/imask.js');
$this->addExternalJS($templateFolder . '/js/InheritItem.js');
$this->addExternalJS($templateFolder . '/js/wrap.js');
$this->addExternalJS($templateFolder . '/js/avansWrapp.js');
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

/**
 * @var $arResult
 */

if (empty($arResult['VALUES'])) {
    $arResult['VALUES'][] = [];
}

foreach ($arResult['COUNTRY'] as $id => $arCountry) {
    if ($arCountry['DEFAULT'] != 'Y') {
        unset($arResult['COUNTRY'][$id]);
        continue;
    }

    $defCountry = $arCountry;
    break;
}

if (empty($defCountry)) {
    ShowError('Ошибка страны плательщика!');
    return;
}

foreach ($arResult['BE_VS_COUNTRY'] as $beId => $countryId) {
    if ($countryId == $defCountry['ID']) {
        continue;
    }

    unset($arResult['ALL_BE'][$beId]);
}

foreach ($arResult['CURRENCY_LIST'] as $currencyId => $currency) {
    if ($currencyId == $defCountry['RATE_ID']) {
        continue;
    }

    unset($arResult['CURRENCY_LIST'][$currencyId]);
}

$arResult['CURRENCY_LIST'][$defCountry['RATE_ID']];
?>

<div id="be-form-wrapp">

    <input type="hidden" name="<?=$arParams['HTML_CONTROL']['VALUE']?>" id="widget_data" class="ui-ctl-element mainSum">

    <div class="be-form" id="be-choose-be-form">

        <div class="article-item be-item">

            <div class="de-inline-bl free-sum be-line">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Получили</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input value="0" name="common-sum" autocomplete="off" type="text" class="ui-ctl-element">
                </div>
            </div>

            <div class="de-inline-bl free-sum be-line">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Входящий остаток</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input value="0" name="incoming-balance" type="text" autocomplete="off" class="ui-ctl-element">
                </div>
            </div>

        </div>

        <div class="wrap-block">
            <div class="article-item be-item clone-html" data-item="true">

                <input name="block-data" type="hidden">

                <div class="article-item-column">

                    <div class="de-inline-bl free-sum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Потратили</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <input value="0" name="spend" type="text" autocomplete="off" class="ui-ctl-element">
                        </div>
                    </div>

                    <div class="de-inline-bl free-sum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Дата</label>
                        </div>

                        <div class="ui-ctl de-block de-label ui-ctl-after-icon ui-ctl-sm ui-ctl-date">
                            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
                            <input value="" name="date" type="text" class="ui-ctl-element" autocomplete="off" onclick="BX.calendar({node: this, field: this, bTime: false});">
                        </div>
                    </div>

                    <div class="de-inline-bl free-sum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Назначение</label>
                        </div>

                        <div class="ui-ctl  ui-ctl-textarea ui-ctl-no-resize">
                            <textarea name="article-description" class="ui-ctl-element"></textarea>
                        </div>
                    </div>

                    <div class="de-inline-bl free-sum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Документ подтверждения</label>
                        </div>

                        <div class="ui-ctl  ui-ctl-textarea ui-ctl-no-resize">
                            <textarea name="document" class="ui-ctl-element"></textarea>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px" class="de-allsum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Статья расходов</label>
                        </div>

                        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                            <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                            <select name="article" class="ui-ctl-element">
                                <option value="0">Не выбрано</option>
                                <?foreach ($arResult['ARTICLES'] as $id => $article):
                                    if ($article['ACTIVE'] != 'Y') {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?=$id?>"><?=$article['NAME']?></option>
                                <?endforeach;?>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;" class="de-allsum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Обновить бюджет по статье</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <button type="button" class="updateBudget de-inline-bl ui-btn ui-btn-success ui-ctl-block ui-btn-sm ui-ctl-w100">Обновить</button>
                        </div>
                    </div>

                    <div class="de-inline-bl free-sum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Свободный остаток</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <input value='0' disabled name='free_sum' type="text"
                                   class="ui-ctl-element">
                        </div>
                    </div>

                    <div class="de-inline-bl free-sum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Остаток (%)</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <input value='100' disabled name='free_percent' type="text"
                                   class="ui-ctl-element">
                        </div>
                    </div>

                    <div class="de-inline-bl free-sum be-line" style="display: none">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Курс валюты плательщика относительно курсов БЕ *:</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                            <input value="1" name='rateCurse' type="text"
                                   class="ui-ctl-element">
                        </div>
                    </div>

                    <div class="de-allsum be-line"  style="display: none">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">
                                Страна БЕ плательщика *:
                            </label>
                        </div>

                        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                            <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                            <select name='country' class="ui-ctl-element">
                                <?foreach ($arResult['COUNTRY'] as $id => $arCountry):?>
                                    <option value="<?=$id?>" <?=($arCountry['DEFAULT'] == 'Y') ? 'selected' : ''?>>
                                        <?=$arCountry['NAME']?>
                                    </option>
                                <?endforeach;?>
                            </select>
                        </div>
                    </div>

                    <div class="de-allsum be-line"  style="display: none">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">
                                Валюта БЕ *:
                            </label>
                        </div>

                        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                            <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                            <select name='rateBe' class="ui-ctl-element">
                                <?
                                /**
                                 * Так как валюта одна, то она же и будет выбранной
                                 */
                                ?>
                                <?foreach ($arResult['CURRENCY_LIST'] as $id => $elemCurrency):?>
                                    <option selected data-rate="<?= $elemCurrency['CODE'] ?>" value="<?= $elemCurrency['ID'] ?>">
                                        <?= $elemCurrency['NAME'] ?>
                                    </option>
                                <?
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="de-allsum be-line"  style="display: none">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">
                                Валюта заявки *:
                            </label>
                        </div>

                        <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                            <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                            <select name='rate' class="ui-ctl-element">
                                <?
                                /**
                                 * Так как валюта одна, то она же и будет выбранной
                                 */
                                ?>
                                <?php
                                foreach ($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                                    ?>
                                    <option selected data-rate="<?= $elemCurrency['CODE'] ?>" value="<?= $elemCurrency['ID'] ?>">
                                        <?= $elemCurrency['NAME'] ?>
                                    </option>
                                <?
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="de-inline-bl ">
                        <button type="button" class="remove-block ui-btn ui-ctl-block ui-btn-sm">Удалить запись</button>
                    </div>

                </div>
                <div class="article-item-column be-column">
                    <div class="be-item no-border">
                        <div class='errors field-wrap'></div>
                        <span class="field-wrap fields boolean" style="max-width: 600px">
                            <span class="field-item fields boolean">
                                <label>
                                    <input type="checkbox" name='distribution' value="1" checked='checked'>
                                    Распределить по всем в равных %
                                </label>
                            </span>
                        </span>
                    </div>

                    <?
                    $beId = 0;
                    $disabledDelete = 'disabled';
                    $arItem = [];
                    ?>
                    <?include 'be.php'?>
                </div>
            </div>
            <div class='cloneBlocks wrap be' style="display: none">
                <? $arItem = []; ?>
                <? $beId = -1 ?>
                <? $forClone = true ?>
                <div class="be-item new" data-id= <?= $beId ?>>

                    <div class="be-line-item">
                        <div class="de-inline-bl de-bi">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Бизнес единица</label>
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

                        <div class="de-inline-bl ">
                            <button type="button" class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm">Еще</button>
                        </div>

                        <div class="de-inline-bl">
                            <button type="button" disabled class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X
                            </button>
                        </div>
                    </div>
                    <div class="be-line-item">
                        <div class="de-inline-bl de-psnt">

                            <div class="ui-entity-editor-block-title de-block">
                                <label class="ui-entity-editor-block-title-text">% затрат</label>
                            </div>

                            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                                <input value="0" type="text" class="percentInput ui-ctl-element">
                            </div>

                        </div>

                        <div class="de-inline-bl de-sum">

                            <div class="ui-entity-editor-block-title de-block">
                                <label class="ui-entity-editor-block-title-text">Сумма</label>
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
                                <input value="<?= $arResult['AR_BUDGETS'][$arItem['id']] ?>" disabled type="text"
                                       class="budgetInput ui-ctl-element" placeholder="сумма">
                            </div>
                        </div>
                    </div>

                    <div class="be-line-item"></div>

                    <div class="de-inline-bl free-sum be-line">
                        <div class="ui-entity-editor-block-title de-block de-label">
                            <label class="ui-entity-editor-block-title-text">Комментарий</label>
                        </div>

                        <div class="ui-ctl ui-ctl-textarea ui-ctl-no-resize">
                            <textarea name="description" class="ui-ctl-element"><?=$arItem['description']?></textarea>
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
            <? foreach ($arResult['VALUES'] as $arValue):?>
                <div class="article-item be-item" data-item="true">

                    <input name="block-data" type="hidden">

                    <div class="article-item-column">

                        <div class="de-inline-bl free-sum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Потратили</label>
                            </div>

                            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                                <input value="<?=$arValue['sum']?>" name="spend" autocomplete="off" type="text" class="ui-ctl-element">
                            </div>
                        </div>

                        <div class="de-inline-bl free-sum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Дата</label>
                            </div>

                            <div class="ui-ctl de-block de-label ui-ctl-after-icon ui-ctl-sm ui-ctl-date">
                                <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
                                <input value="<?=$arValue['date']?>" name="date" type="text" autocomplete="off" class="ui-ctl-element" onclick="BX.calendar({node: this, field: this, bTime: false});">
                            </div>
                        </div>

                        <div class="de-inline-bl free-sum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Назначение</label>
                            </div>

                            <div class="ui-ctl  ui-ctl-textarea ui-ctl-no-resize">
                                <textarea name="article-description" class="ui-ctl-element"><?=$arValue['description']?></textarea>
                            </div>
                        </div>

                        <div class="de-inline-bl free-sum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Документ подтверждения</label>
                            </div>

                            <div class="ui-ctl  ui-ctl-textarea ui-ctl-no-resize">
                                <textarea name="document" class="ui-ctl-element"><?=$arValue['document']?></textarea>
                            </div>
                        </div>

                        <div style="" class="de-allsum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Статья расходов</label>
                            </div>

                            <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                                <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                                <select name="article" class="ui-ctl-element">
                                    <option value="0">Не выбрано</option>
                                    <?php
                                    foreach ($arResult['ARTICLES'] as $id => $article):
                                        if ($article['ACTIVE'] != 'Y' and $id != $arValue['article']) {
                                            continue;
                                        }
                                        ?>
                                        <option value="<?=$id?>" <?=($id == $arValue['article']) ? 'selected' : ''?>>
                                            <?=$article['NAME']?>
                                        </option>
                                    <?endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="margin-bottom: 25px;" class="de-allsum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Обновить бюджет</label>
                            </div>

                            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                                <button type="button" class="updateBudget de-inline-bl ui-btn ui-btn-success ui-ctl-block ui-btn-sm ui-ctl-w100">Обновить</button>
                            </div>
                        </div>

                        <div class="de-inline-bl free-sum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Свободный остаток</label>
                            </div>

                            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                                <input value='0' disabled name='free_sum' type="text"
                                       class="ui-ctl-element">
                            </div>
                        </div>

                        <div class="de-inline-bl free-sum be-line">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Остаток (%)</label>
                            </div>

                            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                                <input value='100' disabled name='free_percent' type="text"
                                       class="ui-ctl-element">
                            </div>
                        </div>

                        <div class="de-inline-bl free-sum be-line"  style="display: none">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">Курс валюты плательщика относительно курсов БЕ *:</label>
                            </div>

                            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                                <input value="1" name='rateCurse' type="text"
                                       class="ui-ctl-element">
                            </div>
                        </div>

                        <div class="de-allsum be-line"  style="display: none">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">
                                    Страна БЕ плательщика *:
                                </label>
                            </div>

                            <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                                <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                                <select name='country' class="ui-ctl-element">
                                    <?php
                                    foreach ($arResult['COUNTRY'] as $id => $arCountry):
                                        $selected = "";
                                        if ($id == $arResult['country'] or (empty($arResult['country']) and $arCountry['DEFAULT'] == 'Y')) {
                                            $selected = 'selected';
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

                        <div class="de-allsum be-line"  style="display: none">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">
                                    Валюта БЕ *:
                                </label>
                            </div>

                            <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                                <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                                <select name='rateBe' class="ui-ctl-element">
                                    <?php
                                    foreach ($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                                        $selected = "";
                                        if ($elemCurrency['CODE'] === $arResult['rateBe']) {
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

                        <div class="de-allsum be-line"  style="display: none">
                            <div class="ui-entity-editor-block-title de-block de-label">
                                <label class="ui-entity-editor-block-title-text">
                                    Валюта заявки *:
                                </label>
                            </div>

                            <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                                <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                                <select name='rate' class="ui-ctl-element">
                                    <?php
                                    foreach ($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                                        $selected = "";
                                        if ($elemCurrency['CODE'] === $arResult['rate']) {
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


                        <div class="de-inline-bl ">
                            <button type="button" class="remove-block ui-btn ui-ctl-block ui-btn-sm">Удалить запись</button>
                        </div>

                    </div>
                    <div class="article-item-column be-column">

                        <div class="be-item be-info no-border">
                            <div class='errors field-wrap'></div>

                            <span class="field-wrap fields boolean" style="max-width: 600px">
                                <span class="field-item fields boolean">
                                    <label>
                                        <input type="checkbox" name='distribution' value="1"
                                               <? if ($arValue['distr'] == 1 or empty($arParams['userField']['VALUE'])): ?>checked='checked'<? endif ?>>
                                            Распределить по всем в равных %
                                    </label>
                                </span>
                            </span>
                        </div>


                        <?if (empty($arValue['items'])):?>
                            <?
                            $beId = 0;
                            $disabledDelete = 'disabled';
                            $arItem = [];
                            ?>
                            <?include 'be.php'?>
                        <?else:?>
                            <?foreach ($arValue['items'] as $arItem):?>
                                <?$beId = $arItem['id']?>
                                <?include 'be.php'?>
                            <?endforeach;?>
                        <?endif;?>
                    </div>
                </div>
            <?endforeach;?>

        </div>

        <div class="article-item be-item form-toolbar">

            <div class="article-item">
                <div class="de-inline-bl ">
                    <button type="button" class="add-block ui-btn ui-btn-success ui-ctl-block ui-btn-md">Добавить</button>
                </div>

                <div class="de-inline-bl ">
                    <div>
                        <div class="be-line approving-remove-all">
                            <div>
                                Удалить все записи?
                            </div>
                            <div class="be-line">
                                <button type="button" class="remove-block-all-apply ui-btn-danger ui-btn ui-ctl-block ui-btn-md">Да</button>
                                <button type="button" class="remove-block-all-cancel ui-btn-success ui-btn ui-ctl-block ui-btn-md">Отмена</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="remove-block-all ui-btn ui-ctl-block ui-btn-md">Удалить все записи</button>
                    </div>
                </div>
            </div>

        </div>

        <div class="article-item be-item">
            <div class="de-inline-bl free-sum be-line">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Итого расход</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block ui-ctl-w100">
                    <input disabled value="0" name="total-sum" type="text" autocomplete="off" class="ui-ctl-element">
                </div>
            </div>

            <div class="de-inline-bl free-sum be-line">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Сдано в кассу</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block ui-ctl-w100">
                    <input value="0" name="cash-sum" type="text" autocomplete="off" class="ui-ctl-element">
                </div>
            </div>

            <div class="de-inline-bl free-sum be-line">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Остаток/Перерасход</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block ui-ctl-w100">
                    <input disabled value="0" name="overspending" type="text" autocomplete="off" class="ui-ctl-element">
                </div>
            </div>
        </div>

        <div class="article-item be-item form-toolbar error-block">

        </div>
    </div>
</div>

<script type="text/javascript">
    BX(function () {
        const instanceAvans = new AvansWrapp({
            wrap: document.querySelector("#be-form-wrapp"),
            mainWrap: document.querySelector("#be-form-wrapp"),
            mainInputSelector: 'input[name="<?=$arParams['HTML_CONTROL']['VALUE']?>"]',
            props: <?=\CUtil::PhpToJSObject($arParams['PROPERTIES'] ?? [])?>,
            draftId: '<?=$arParams['DRAFT_ID']?>',
            cursRate: '<?=$arResult['RATE_CURSE']?>',
            formSelector: '<?=(empty($arParams['PROPERTY']['ELEMENT_ID']))
                ? "form_lists_element_add_{$arParams['PROPERTY']['IBLOCK_ID']}"
                : "form_lists_element_edit_{$arParams['PROPERTY']['IBLOCK_ID']}"?>',
            articlesList: <?=\CUtil::PhpToJSObject(array_keys($arResult['ARTICLES']))?>,
            countryId: '<?=$defCountry['ID']?>',
            rateId: '<?=$defCountry['RATE_ID']?>',
            createFromParent: <?=\CUtil::PhpToJSObject(!empty($arParams['PARENT']))?>,
            autoCreate: <?=\CUtil::PhpToJSObject($arParams['PARENT']['AUTO_CREATE'] == 'Y' ?? false)?>,
            allMonth: <?=\CUtil::PhpToJSObject(\Immo\Tools\ActivityTools::getAllNameMonth())?>,
            financialYear: <?=\Immo\Iblock\Property\BiznesUnitsIblockField::defineFinancialYear()?>
        });

        if (!window.hasOwnProperty('AvansBeInstance')) {
            window['AvansBeInstance'] = {};
        }

        const elementId = '<?=$arParams['PROPERTY']['ELEMENT_ID']?>';
        window['AvansBeInstance'][elementId] = instanceAvans;
    });
</script>
