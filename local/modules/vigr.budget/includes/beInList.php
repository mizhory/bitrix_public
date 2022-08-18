<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div>
    Общая сумма - <?=$arParseValues['MAIN_SUM']?><br>
    Статья - <?=$arParseValues['ARTICLES'][$arParseValues['ARTICLE']]?><br>
    Страна - <?=$arParseValues['COUNTRY'][$arParseValues['COUNTRY_NOW']]['NAME']?><br>
    Валюта БЕ - <?=$arParseValues['RATE']?><br>
    Валюта плательщика - <?=$arParseValues['RATE_BE']?><br>
</div>
<br>
<?foreach ($arParseValues['ITEMS'] as $key=>$arItem):?>
    <div>
        <?=$arParseValues['ALL_BE'][$key]?> , Сумма - <?=$arItem['sum']?> , Процент - <?=$arItem['percent']?>
        <?if($arItem['items']):?>
            <br>
            Продукты : <br>
            <?foreach ($arItem['items'] as $subKey=>$arSubItem):?>
                <div>
                    <?=$arParseValues['ALL_BE_PRODUCTS'][$key][$subKey]?> , Сумма - <?=$arSubItem['sum']?> , Процент - <?=$arSubItem['percent']?>
                </div>
            <?endforeach;?>
        <?endif;?>
    </div>
    <br>
<?endforeach;?>

