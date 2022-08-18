<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult)):
    ?>

    <?if ($arParams['IS_LIST']):?>
            Создан по шаблону
        <?if ($arResult['INCLUDE_HTML']):?>
            <input type="hidden" name="<?=$arResult['HTML_CONTROL']['VALUE']?>" value="<?=$arResult['VALUE']['VALUE']?>">
        <?endif?>
    <?else:?>
        <?if (!$arResult['INCLUDE_HTML']):?>
            Создано по шаблону - <?=$arResult['VALUE']['NAME']?>
        <?elseif (!empty($arResult['VALUE']['LINK'])):?>
            <b>Создано по шаблону -
                <a href="<?=$arResult['VALUE']['LINK']?>" target="_blank">
                    <?=$arResult['VALUE']['NAME']?>
                </a>
            </b>
        <?else:?>
            <b>Создано по шаблону - <?=$arResult['VALUE']['NAME']?></b>
        <?endif?>

        <?if ($arResult['INCLUDE_HTML']):?>
            <input type="hidden" name="<?=$arResult['HTML_CONTROL']['VALUE']?>" value="<?=$arResult['VALUE']['VALUE']?>">
        <?endif?>
    <?endif;?>

<?php endif;
