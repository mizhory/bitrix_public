<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ITEMS'])):?>

    <div style="position: relative">
        <span class="status-card-wrapper" id="<?=$arResult['ID']?>">
            <?$i = 0?>
            <? foreach ($arResult['ITEMS'] as $id => $arItem):
                if ($i != 0):?>
                    <span class="status-card-delimiter">></span>
                <?endif;
                ++$i;?>
                <span
                    class="status-card-item<?=($arItem['ACTIVE']) ? ' active' : ''?>"
                    data-name="<?=$arItem['NAME']?>"
                    data-id="<?="{$arResult['ID']}-id-{$arItem['ID']}"?>"
                    <?=(!empty($arItem['COLOR'])) ? "data-color='{$arItem['COLOR']}'" : ''?>
                    <?$styleRow = ''?>
                    <?if (!empty($arResult['ITEMS'][$arResult['VALUE']]) and $arItem['ACTIVE']) {
                        $styleRow = "background:{$arResult['ITEMS'][$arResult['VALUE']]['COLOR']}";
                    } else {
                        $styleRow = "border-bottom: 3px solid {$arItem['COLOR']}";
                    }?>
                    style="<?=$styleRow?>"
                ><?=$arItem['NAME']?></span>
            <? endforeach; ?>
        </span> 
    </div>

    <input type="hidden" name="<?=$arResult['HTML_CONTROL']['VALUE']?>" value="<?=$arResult['VALUE']?>">

    <?if (!empty($item = $arResult['ITEMS'][$arResult['VALUE']])):?>
        <span class="status-card-title">
            <b><?=$item['NAME']?><?=(!empty($arResult['NAME_FAIL_STATUS']))
                ? " на стадии \"{$arResult['NAME_FAIL_STATUS']}\""
                : ''?>
            </b>
        </span>
    <?endif;?>

<? else: ?>

<b>Статусы не найдены</b>

<? endif; ?>