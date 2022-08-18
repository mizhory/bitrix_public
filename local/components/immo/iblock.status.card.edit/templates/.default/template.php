<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ITEMS'])):?>

    <span class="status-card-wrapper" id="<?=$arResult['ID']?>">
        <? foreach ($arResult['ITEMS'] as $id => $arItem): ?>
            <span
                class="status-card-item<?=($arItem['ACTIVE']) ? ' active' : ''?>"
                data-name="<?=$arItem['NAME']?>"
                data-id="<?="{$arResult['ID']}-id-{$arItem['ID']}"?>"
                <?=(!empty($arItem['COLOR'])) ? "data-color='{$arItem['COLOR']}'" : ''?>
                <?=(!empty($arResult['ITEMS'][$arResult['VALUE']] and $arItem['ACTIVE']) ? "style='background:{$arResult['ITEMS'][$arResult['VALUE']]['COLOR']};'" : '')?>
            ></span>
        <? endforeach; ?>
    </span>

    <?if (!empty($item = $arResult['ITEMS'][$arResult['VALUE']])):?>
        <span class="status-card-title">
            <?=$item['NAME']?><?=(!empty($arResult['NAME_FAIL_STATUS']))
                ? " на стадии \"{$arResult['NAME_FAIL_STATUS']}\""
                : ''?>
        </span>
    <?endif;?>

    <script type="text/javascript">
        BX(function() {
            let statusCard = window.StatusCard.getInstance('<?=$arResult['ID']?>');
            statusCard.init();
        });
    </script>

<? else: ?>

<b>Статусы не найдены</b>

<? endif; ?>