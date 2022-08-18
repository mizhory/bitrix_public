<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->setTitle('Бюджет');
?>

<div class="sitemap-content">
    <div class="sitemap-section">
        <a class="sitemap-section-title" href="all/">Общий бюджет БЕ</a>
        <div class="sitemap-section-items">
            <a class="sitemap-section-item" href="download/">Загрузка файлов</a>
            <a class="sitemap-section-item" href="history/">История действий с бюджетом</a>
        </div>
    </div>

    <?if (!empty($arResult['ELEMENTS'])):?>
        <div class="sitemap-section">
            <div class="sitemap-section-items">
                <?foreach ($arResult['ELEMENTS'] as $arElement):?>
                    <a class="sitemap-section-item" href="detail/<?=$arElement['ID']?>/"><?=$arElement['NAME']?></a>
                <?endforeach;?>
            </div>
        </div>
    <?endif;?>
</div>