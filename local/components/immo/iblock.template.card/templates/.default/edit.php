<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult)):?>

    <?if ($arParams['IS_LIST']):?>
        <?if ($arResult['INCLUDE_HTML']):?>
            <label>
                <?=($arResult['VALUE']['VALUE'] == 'IS_TEMPLATE') ? 'Является шаблоном' : 'Не является шаблоном'?>
            </label>
        <?else:?>
            <?=($arResult['VALUE']['VALUE'] == 'IS_TEMPLATE') ? 'Является шаблоном' : 'Не является шаблоном'?>
        <?endif ?>
    <?else:?>
        <label>
            Является шаблоном
            <input
                    name="<?=$arResult['HTML_CONTROL']['VALUE']?>"
                    type="checkbox"
                    value="IS_TEMPLATE"
                <?=($arResult['VALUE']['VALUE'] == 'IS_TEMPLATE') ? 'checked' : ''?>
            >
        </label>
    <?endif?>

<?php endif;
