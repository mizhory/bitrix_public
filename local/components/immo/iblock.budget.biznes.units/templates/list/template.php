<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$isPrint = ($arParams['PRINT'] == 'Y');
if (!empty($arResult)):
    if ($arParams['MULTIPLE'] != 'Y') {
        foreach ($arResult['ITEMS'] as $key => $arItem) {
            $arItem['sum'] = str_replace('.', ',', $arItem['sum']);
            $arItem['percent'] = str_replace('.', ',', $arItem['percent']);

            echo (!$isPrint) ? '<div>' : PHP_EOL;
            $be = "{$arResult['ALL_BE'][$key]['NAME']} Сумма - {$arItem['sum']} Процент - {$arItem['percent']}";
            echo $be;

            if (!empty($arItem['items'])) {
                echo ($isPrint) ? \Immo\Iblock\Property\BiznesUnitsIblockField::TEMPLATE_DELIMITER : '';
                foreach ($arItem['items'] as $subKey => $arSubItem) {
                    $arSubItem['sum'] = str_replace('.', ',', $arSubItem['sum']);
                    $arSubItem['percent'] = str_replace('.', ',', $arSubItem['percent']);

                    echo (!$isPrint) ? '<br>' : '';
                    $product = "{$arResult['ALL_BE_PRODUCTS'][$key][$subKey]['NAME']} Сумма - {$arSubItem['sum']} Процент - {$arSubItem['percent']}";
                    echo $product;
                    echo (!$isPrint) ? '' : PHP_EOL;
                }
            }

            echo (!$isPrint) ? '</div>' : '';
            echo (!$isPrint) ? '<br>' : PHP_EOL;
        }
    } else {
        foreach ($arResult['VALUES'] as $arBe):
            if ($isPrint):
                foreach ($arBe['items'] as $index => $valueBe):
                    echo \Immo\Iblock\Property\BiznesUnitsIblockField::TEMPLATE_BE_DELIMITER;

                    $sum = (!empty($arParams['BE_RATE']))
                    ? "{$valueBe['sum']} {$arParams['BE_RATE']['CODE']}"
                    : $valueBe['sum'];

                    $be = "{$arResult['ALL_BE'][$valueBe['id']]['NAME']} - {$sum}";
                    echo $be . PHP_EOL;

                    if (!empty($valueBe['items'])):
                        echo \Immo\Iblock\Property\BiznesUnitsIblockField::TEMPLATE_DELIMITER;

                        foreach ($valueBe['items'] as $id => $item):
                            $item['sum'] = str_replace('.', ',', $item['sum']);
                            $product = "{$arResult['ALL_BE_PRODUCTS'][$valueBe['id']][$id]['NAME']} - {$item['sum']} {$arParams['BE_RATE']['CODE']}";
                            echo $product . PHP_EOL;
                        endforeach;
                    endif;
                endforeach;
            else:
                $index = 0;
                foreach ($arBe['items'] as $key=>$arItem):
                    ++$index;
                    $arItem['sum'] = str_replace('.', ',', $arItem['sum']);
                    $arItem['percent'] = str_replace('.', ',', $arItem['percent']);
                    ?>
                    <?=($index != 1) ? '<br>' : ''?>
                    <?=$arResult['ALL_BE'][$key]['NAME']?> Сумма - <?=$arItem['sum']?> Процент - <?=$arItem['percent']?>
                    <?if($arItem['items']):?>
                        <br>
                        Продукты:
                        <?foreach ($arItem['items'] as $subKey=>$arSubItem):
                        $arSubItem['sum'] = str_replace('.', ',', $arSubItem['sum']);
                        $arSubItem['percent'] = str_replace('.', ',', $arSubItem['percent']);
                        ?>
                            <br>
                            <?=$arResult['ALL_BE_PRODUCTS'][$key][$subKey]['NAME']?> Сумма - <?=$arSubItem['sum']?> Процент - <?=$arSubItem['percent']?>
                        <?endforeach;?>
                    <?endif;?>
                <?endforeach;?>
                <br>
                <br>
            <?endif;
        endforeach;
    }
endif;
