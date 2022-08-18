<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$this->addExternalJS('/local/modules/vigr.budget/js/imask.js');
$this->addExternalJS('/local/modules/vigr.budget/js/be/new2/wrap.js');
$this->addExternalJS('/local/modules/vigr.budget/js/be/new2/be.js');
$this->addExternalJS('/local/modules/vigr.budget/js/be/new2/beProduct.js');

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

$year = date('Y');


if($month <= 4){
    $year--;
}

/**
 * @var $arResult
 */

?>

<input class='countItems' type='hidden' value='5'>

<input type='hidden' id='nowMonth' value='<?= $arr[$month] ?>'>
<input type='hidden' id='nowYear' value='<?= $year ?>'>

<input value="<?= $arResult['DRAFT'] ?>" type='hidden' id='draftI'>
<input value="<?= $arResult['FD'] ?>" type='hidden' id='FD'>
<input value="<?= $arResult['ARTICLE'] ?>" type='hidden' id='articleI'>
<input value="<?= $arResult['YEAR'] ?>" type='hidden' id='yearI'>
<input value="<?= $arResult['MONTH'] ?>" type='hidden' id='monthI'>

<div class="be-form" id="be-choose-be-form">
    <div class="be-item" id="be_sum">
        <div>
            <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                <input name="UF_CRM_BIZUINIT_WIDGET_2" type="hidden" id="widget_data" class="ui-ctl-element mainSum">
            </div>

            <div style="margin-bottom: 25px" class="de-allsum">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">
                        Плательщик (Юр. Лицо) (список юр. лиц) *</label>
                </div>

                <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                    <select id='urList' class="ui-ctl-element">
                        <option value="N">не выбрана</option>
                        <?php
                        foreach ($arResult['UR_LISTS'] as $id => $elemCurrency):
                            $selected = "";
                            if ($id == $arResult['UR']) {
                                $selected = 'selected';
                            }
                            ?>
                            <option <?= $selected ?>
                                    data-countryId="<?= $arResult['BE_VS_COUNTRY'][$arResult['UR_LISTS_VS_BE'][$id]] ?>"
                                    value="<?= $id ?>"><?= $elemCurrency ?></option>
                        <?
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 25px" class="de-allsum">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">
                        Страна БЕ плательщика *
                    </label>
                </div>

                <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                    <select disabled id='country' class="ui-ctl-element">
                        <option value="N">не выбрана</option>
                        <?php
                        foreach ($arResult['COUNTRY'] as $id => $arCountry):
                            $selected = "";
                            if ($id == $arResult['COUNTRY_NOW']) {
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



            <div style="margin-bottom: 25px" class="de-allsum">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">
                        Валюта БЕ *
                    </label>
                </div>

                <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                    <select id='rateBe' class="ui-ctl-element">
                        <option value="N">не выбрана</option>
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

            <div style="margin-bottom: 25px" class="de-allsum">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">
                        Статья расходов *
                    </label>
                </div>

                <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                    <select id='article' class="ui-ctl-element">
                        <option value="N">не выбрана</option>
                        <?php
                        foreach ($arResult['ARTICLES'] as $id => $elemCurrency):
                            $selected = "";
                            if ($id == $arResult['ARTICLE']) {
                                $selected = 'selected';
                            }
                            ?>
                            <option <?= $selected ?> value="<?= $id ?>" <?= $selected ?>><?= $elemCurrency ?></option>
                        <?
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 25px" class="de-allsum">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">
                        Валюта заявки *
                    </label>
                </div>

                <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                    <select id='rate' class="ui-ctl-element">
                        <option value="N">не выбрана</option>
                        <?php
                        foreach ($arResult['CURRENCY_LIST'] as $id => $elemCurrency):
                            $selected = "";
                            if ($elemCurrency['CODE'] === $arResult['RATE']) {
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

            <div class="de-inline-bl de-allsum">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Сумма *</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block"> <!-- 1. Основной контейнер -->
                    <input value="<?= $arResult['MAIN_SUM'] ?? 0 ?>" type="text" class="ui-ctl-element sumInput">
                    <!-- 2. Основное поле -->
                </div>
            </div>

            <div class="de-inline-bl free-sum" style="padding-top: 10px">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Свободный остаток : </label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input value='<?= $arResult['FREE_SUM'] ?? 0 ?>' disabled id='free_sum' type="text"
                           class="ui-ctl-element">
                </div>
            </div>

            <div class="de-inline-bl free-sum" style="padding-top: 10px">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Остаток (%) : </label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input value='<?= $arResult['FREE_PERCENT'] ?? 100 ?>' disabled id='free_percent' type="text"
                           class="ui-ctl-element">
                </div>
            </div>

            <div class="de-inline-bl free-sum" style="padding-top: 10px">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Курс валюты плательщика относительно курсов БЕ
                        : </label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input value="<?= $arResult['RATE_CURSE'] ?? '1' ?>" id='rateCurse' type="text"
                           class="ui-ctl-element">
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
            <div style="color:green;display: none" class='success'>
                Все атлична!
            </div>
            <div style="color: red" class='errors'>

            </div>
        </div>
    </div>
    <span class="field-wrap fields boolean">
        <span class="field-item fields boolean">
        <label style="width: 230px">
            <input type="checkbox" id='distribution' value="1"
                   <? if ($arResult['DISTR'] == 1): ?>checked='checked'<? endif ?>>
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
    <div class='cloneBlocks wrap be' style="display: none">
        <? $arItem = []; ?>
        <? $beId = -1 ?>
        <? $forClone = true ?>
        <div class="be-item new" data-id= <?= $beId ?>>

            <div class="de-inline-bl de-bi">
                <div class="ui-entity-editor-block-title de-block de-label">
                    <label class="ui-entity-editor-block-title-text">Бизнес единица</label>
                </div>

                <div class="ui-ctl ui-ctl-after-icon ui-ctl-block ui-ctl-dropdown ui-ctl-sm">

                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                    <select class="ui-ctl-element select-item">
                        <option data-rate='' value="0">Не выбрано</option>
                        <? foreach ($arResult['ALL_BE'] as $idBe => $nameBe): ?>
                            <?
                            $country = $arResult['BE_VS_COUNTRY'][$idBe];
                            $rateId = $arResult['COUNTRY'][$country]['RATE_ID'];
                            $rateCode = $arResult['CURRENCY_LIST'][$rateId]['CODE'];

                            $selected = '';
                            if ($arItem['id'] == $idBe) {
                                $selected = 'selected';
                            }
                            ?>
                            <option data-rate="<?= $rateCode ?>" <?= $selected ?> value="<?= $idBe ?>"><?= $nameBe ?>
                                - <?= $arResult['CURRENCY_LIST'][$rateId]['CODE'] ?></option>
                        <? endforeach; ?>
                    </select>
                </div>
            </div>

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

            <div class="de-inline-bl de-sum" style="">
                <div class="ui-entity-editor-block-title de-block">
                    <label class="ui-entity-editor-block-title-text">Бюджет</label>
                </div>

                <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-block">
                    <input value="<?= $arResult['AR_BUDGETS'][$arItem['id']] ?>" disabled type="text"
                           class="budgetInput ui-ctl-element" placeholder="сумма">
                </div>
            </div>

            <div class="de-inline-bl ">
                <button class="add ui-btn ui-btn-success ui-ctl-block ui-btn-sm">Еще</button>
            </div>

            <div class="de-inline-bl">
                <button disabled class="delete ui-btn ui-btn-light-border ui-ctl-block ui-btn-sm">X
                </button>
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

<style>
    .error-option {
        background: grey;
        opacity: 50%;
    }
</style>
<? if ($_REQUEST['mode'] === 'main.edit'): ?>
    <script>
        try {
            window.bizUnitClass2 = new WrapItems(
                {'wrap': document.querySelector("[data-cid='UF_CRM_BIZUINIT_WIDGET_2']")}
            );
        } catch (error) {
            console.log('Ошибка при создании класса редактирования!');
            console.log(error);
        }

    </script>
<? endif ?>