<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
$arJS = ['ui.buttons', 'entity-editor', 'fx', 'ui.sidepanel', "sidepanel", 'ui.cnt', 'ui.forms', 'jquery3', "jq.inputmask"];
\CJSCore::init($arJS); ?>
<script>
    function createMask(input, callback = {}, scale = 2, negative = false) {
        let mask = IMask(input, {scale: scale, mask: Number, thousandsSeparator: ' ', radix: ',', signed: negative});
        if (!!callback && typeof callback == 'function') {
            mask.on('complete', callback);
        }
        return mask;
    }

    $(document).ready(function () {
        var salary_element_dom = document.getElementById('immo-elem-be-salary');
        var ad_salary_element_dom = document.getElementById('immo-elem-be-ad-salary');
        var ov_salary_element_dom = document.getElementById('immo-elem-be-ov-salary');
        var balance_element_dom = document.getElementById('immo-elem-balance');

        createMask(salary_element_dom);
        createMask(ad_salary_element_dom);
        createMask(ov_salary_element_dom);
        createMask(balance_element_dom);
    })

</script>
<div class="bg-white p-4 cs-field">
    <div class="flex mt-2 mb-2">
        <div class="flex items-center mr-4 w-32">
            БЕ по зарплате
        </div>
        <div class="ml-4">
            <select name="UF_CS_BE[salaryBeSelect]" class="salary-be">
                <? foreach ($arResult['DEP_ITEMS'] as $arDepItem): ?>
                    <option<?= ($arDepItem['ID'] == $arResult['FIELD_VALUE']['salaryBeSelect']) ? " selected='selected'" : false ?>
                            value="<?= $arDepItem['ID'] ?>"><?= $arDepItem['NAME'] ?></option>
                <? endforeach; ?>
            </select>
        </div>
    </div>
    <div class="flex mt-2 mb-2">
        <div class="flex items-center mr-4 w-32">
            Оклад по офферу
        </div>
        <div class="ml-4">
            <input name="UF_CS_BE[salary]"
                   id="immo-elem-be-salary"
                   type="text"
                   class="salary immo-salary-ajax-handler"
                   value="<?= $arResult['FIELD_VALUE']["salary"] ?>"/>
        </div>
    </div>
    <div class="flex mt-2 mb-2">
        <div class="flex items-center mr-4 w-32">
            Дополнительный оклад
        </div>
        <div class="ml-4">
            <input
                    type="text"
                    id="immo-elem-be-ad-salary"
                    class="additional-salary"
                    disabled="disabled"
                    value="<?= $arResult['FIELD_VALUE']['additionalSalary'] ?>"/>
        </div>
    </div>
    <div class="flex mt-2 mb-2">
        <div class="flex items-center mr-4 w-32">
            Переплата
        </div>
        <div class="ml-4">
            <input
                    name="UF_CS_BE[over-salary]"
                    id="immo-elem-be-ov-salary"
                    type="text"
                    class="over-salary"
                    value="<?= $arResult['FIELD_VALUE']['over-salary'] ?>"/>
        </div>
    </div>
    <div class="flex mt-2 mb-2">
        <div class="flex items-center mr-4 w-32">
            Остаток до 15 процентов
        </div>
        <div class="ml-4">
            <input
                    name="UF_CS_BE[balance]"
                    id="immo-elem-balance"
                    disabled="disabled"
                    type="text"
                    class="balance"
                    value="<?= $arResult['FIELD_VALUE']['balance'] ?>"/>
        </div>
    </div>
</div>
