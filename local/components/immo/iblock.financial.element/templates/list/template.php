<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ELEMENT'])):?>

    <a href="<?=$arResult['ELEMENT']['DETAIL_PAGE_URL']?>"><?=$arResult['ELEMENT']['NAME']?></a>

<?endif;
