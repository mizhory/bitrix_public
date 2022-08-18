<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? global $APPLICATION; ?>
<? IncludeTemplateLangFile(__FILE__); ?>
<html>
<head><? $APPLICATION->ShowHead(); ?></head>
<body>
<?

use Bitrix\Main\Page\Asset;

$arJS = [
    'ui.buttons', 'entity-editor', 'fx',
    'ui.sidepanel', "sidepanel", 'ui.cnt',
    'ui.forms', 'jquery3'
];
\CJSCore::init($arJS);
Asset::getInstance()->addCss('/local/assets/css/immo_user_card_be_edit_template.css');
Asset::getInstance()->addJs('/local/assets/js/template.user-card-js.js');
Asset::getInstance()->addJs('/local/modules/vigr.budget/js/imask.js');
?>
<? if (isset($arResult['ERRORS']) && count($arResult['ERRORS']) > 0): ?>
    <? foreach ($arResult['ERRORS'] as $k => $ItemErrorMessage): ?>
        <div style="color: #8c0707; font-size: 14px">
            <?= $ItemErrorMessage ?>
        </div>
    <? endforeach;
    return;endif; ?>
<script>
    /**
     * Функция накидывания маски для редактирвания полей в денежном формате
     *
     * @param input
     * @param callback
     * @param scale
     * @param negative
     * @returns {InputMask}
     */
    function createMask(input, callback = {}, scale = 2, negative = false) {
        let mask = IMask(input, {scale: scale, mask: Number, thousandsSeparator: ' ', radix: ',', signed: negative});
        if (!!callback && typeof callback == 'function') {
            mask.on('complete', callback);
        }
        return mask;
    }

    /**
     * Инициализация документа и накидывания маски
     *
     */
    $(document).ready(function () {
        var salary_element_dom = document.getElementById('immo-elem-be-salary');
        var ad_salary_element_dom = document.getElementById('immo-elem-be-ad-salary');
        var ov_salary_element_dom = document.getElementById('immo-elem-be-ov-salary');
        var balance_element_dom = document.getElementById('immo-elem-balance');
        createMask(salary_element_dom);
        createMask(ad_salary_element_dom);
        createMask(ov_salary_element_dom);
        createMask(balance_element_dom);
    });</script>
<?
$arExtendedAccessResponse = \IMMO\Manager\UserAccessManager::expandedAcceptResponse($arParams['USER_ID']);
?>
<? if ($arExtendedAccessResponse == false): ?>
    <h3 style="color:#ff3f3f"><?= getMessage('NOT_ACCESS') ?></h3>
    <? return;
else:
    $HRAccess = $arExtendedAccessResponse[\IMMO\Manager\UserAccessManager::FILED_ACCEPT_HR_CODENAME];
    $overSalaryAccess = $arExtendedAccessResponse[\IMMO\Manager\UserAccessManager::FIELD_ACCEPT_OVER_SALARY];
    $superVisingAccess = $arExtendedAccessResponse[\IMMO\Manager\UserAccessManager::FIELD_ACCEPT_SUPERVISING_MANAGERS];
endif; ?>
<div class="immo-ui-slider-page">
    <div class="immo-ui-page-slider-content">
        <div class="ui-side-panel-wrap-title-wrap">
            <h1 class="ui-side-panel-wrap-title-name-item ui-side-panel-wrap-title-name">Редактирование
                пользователя <b>#<?= $arParams['USER_ID'] ?></b>
                - <?= $arResult['USER']['LAST_NAME'] . " " . $arResult['USER']['NAME'] ?></h1>
        </div>
        <form class="form-edit-usercard-be" method="get" action="?" name="form-edit-usercard-be">
            <input type="hidden" name="USER_ID" value="<?= $arParams['USER_ID'] ?>"/>
            <div class="bg-white p-4">
                <div class="flex mt-2 mb-2">
                    <div class="flex mt-2 mb-2">
                        <div class="flex items-center immo-itemlabel mr-4 w-32">Имя</div>
                        <div class="ml-4"><b><?= $arResult['USER']['NAME'] ?></b></div>
                    </div>
                    <div class="flex mt-2 mb-2">
                        <div class="flex items-center immo-itemlabel mr-4 w-32">Фамилия</div>
                        <div class="ml-4"><b><?= $arResult['USER']['LAST_NAME'] ?></b></div>
                    </div>
                    <div class="flex mt-2 mb-2">
                        <div class="flex items-center immo-itemlabel mr-4 w-32">Отчество</div>
                        <div class="ml-4"><b><?= $arResult['USER']['SECOND_NAME'] ?></b></div>
                    </div>
                    <div class="flex mt-2 mb-2">
                        <div class="flex items-center immo-itemlabel mr-4 w-32">Контактный Email</div>
                        <div class="ml-4"><b><?= $arResult['USER']['EMAIL'] ?></b></div>
                    </div>
                    <? if ($HRAccess == true || $superVisingAccess == true): ?>
                        <div class="flex mt-2 mb-2">
                            <div class="flex items-center immo-itemlabel mr-4 w-32">БЕ по зарплате</div>
                            <div class="ml-4">
                                <select class="salary-be ui-ctl-element" name="salaryBeSelect">
                                    <? foreach ($arResult['BE_ITEMS'] as $id => $beElement): ?>
                                        <option value="<?= $id ?>" <?= $arResult['DATA']['salaryBeSelect'] == $id ? 'selected' : '' ?>><?= $beElement ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <? endif; ?>
                    <? if ($HRAccess == true || $superVisingAccess == true): ?>
                        <div class="flex mt-2 mb-2">
                            <div class="flex items-center immo-itemlabel mr-4 w-32">Оклад по офферу</div>
                            <div class="ml-4">
                                <input type="text" name="salary" id="immo-elem-be-salary" class="salary ui-ctl-element"
                                       value="<?= $arResult['DATA']['salary'] ?>"/>
                            </div>
                        </div>
                    <? endif; ?>
                    <? if ($HRAccess == true || $superVisingAccess == true): ?>
                        <div class="flex mt-2 mb-2">
                            <div class="flex items-center immo-itemlabel mr-4 w-32">Дополнительный оклад</div>
                            <div class="ml-4">
                                <input type="text" class="additional-salary ui-ctl-element" id="immo-elem-be-ad-salary"
                                       disabled="disabled"
                                       value="<?= $arResult['DATA']['additionalSalary'] ?>"/>
                            </div>
                        </div>
                    <? endif; ?>
                    <? if ($HRAccess == true || $superVisingAccess == true || $overSalaryAccess == true): ?>
                        <div class="flex mt-2 mb-2">
                            <div class="flex items-center immo-itemlabel mr-4 w-32">Переплата</div>
                            <div class="ml-4">
                                <input type="text" class="over-salary ui-ctl-element" id="immo-elem-be-ov-salary"
                                       name="overSalary"
                                       value="<?= $arResult['DATA']['overSalary'] ?>"/>
                            </div>
                        </div>
                    <? endif; ?>
                    <? if ($HRAccess == true || $superVisingAccess == true): ?>
                        <div class="flex mt-2 mb-2">
                            <div class="flex items-center immo-itemlabel mr-4 w-32">Остаток до 15 процентов</div>
                            <div class="ml-4">
                                <input type="text" class="balance ui-ctl-element" id="immo-elem-balance"
                                       disabled="disabled"
                                       value="<?= $arResult['DATA']['balance'] ?>"/>
                            </div>
                        </div>
                    <? endif; ?>
                    <? if ($HRAccess == true || $superVisingAccess == true): ?>
                        <div class="flex mt-2 mb-2">
                            <div class="flex items-center immo-itemlabel mr-4 w-32">Тип сотрудника для ЗПВ</div>
                            <div class="ml-4">
                                <?= $arResult['ITEMS']['UF_USER_TYPE_HTML'] ?>
                            </div>
                        </div>
                    <? endif; ?>
                </div>
        </form>
        <div class="immo-functional-block ui-btn-container ui-btn-container-center ui-entity-section ui-entity-section-control">
            <button type="button" class="ui-btn ui-btn-success saveclick">Сохранить</button>
            <button type="button" class="ui-btn ui-btn-default closeclick">Отмена</button>
            <input type="submit" style="display: none">
        </div>
    </div>
</div>
</body>
</html>