<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
//TODO: Подгружаем языковые файлы
IncludeTemplateLangFile(__FILE__);

//TODO: Подгружаем необходимые расширения
\CJSCore::Init(array("slider.user.card", 'cs-be.core'));

?>
<? if (!IMMO\Manager\UserAccessManager::getAccessByUserID($arParams['userField']['ENTITY_VALUE_ID'])): ?>
    <div style="color: #8c0707; font-size: 14px">
        <?= getMessage('NO_ACCESS_FOR_USER') ?>
    </div>
    <? return; ?>
<? endif; ?>
<? if ($arResult['PERMS']) { ?>
    <script>
        var user_id = '<?=$arParams['userField']['ENTITY_VALUE_ID']?>';
        /**
         * Запускае функцию подгрузки БЕ после готовности и полной загрузки документа
         */
        $(document).ready(function () {
            loaderBE('', user_id)
        });
    </script>
    <div>
        <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
            <div class="flex items-center mr-4 w-32 ml-4">
                БЕ по зарплате
            </div>
            <div class="ml-4 salary-be"></div>
        </div>
        <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
            <div class="flex items-center mr-4 w-32 ml-4">
                Оклад по офферу
            </div>
            <div class="ml-4">
                <?= $arResult['FIELD_VALUE']['salary'] ?>
            </div>
        </div>
        <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
            <div class="flex items-center mr-4 w-32 ml-4">
                Дополнительный оклад
            </div>
            <div class="ml-4">
                <?= $arResult['FIELD_VALUE']['additionalSalary'] ?>
            </div>
        </div>
        <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
            <div class="flex items-center mr-4 w-32 ml-4">
                Переплаты
            </div>
            <div class="ml-4">
                <?= $arResult['FIELD_VALUE']['overSalary'] ?>
            </div>
        </div>
        <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
            <div class="flex items-center mr-4 w-32 ml-4">
                Остаток до 15%
            </div>
            <div class="ml-4">
                <?= number_format(str_replace(' ', '', $arResult['FIELD_VALUE']['balance']), 0, ' ', ' ') ?>
            </div>
        </div>
    </div>
    <script>inject('<?=$arParams['userField']['ENTITY_VALUE_ID']?>');</script>
<? } else { ?>
    <div>Информация недоступна</div>
<? } ?>
