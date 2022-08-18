<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

function echoArray(array $array, int $tabCount = 0) {
    $tab = ($tabCount <= 0) ? '' : str_repeat('    ', $tabCount);

    foreach ($array as $index => $item) {
        if (is_array($item)) {
            echo "{$tab}'{$index}' => [" . PHP_EOL;
            $newCount = $tabCount;
            echoArray($item, ++$newCount);
            echo "{$tab}]," . PHP_EOL;
        } else {
            echo "{$tab}'{$index}' => '{$item}'," . PHP_EOL;
        }
    }
}

if (!empty($arParams['FIELDS']['CODE'])) {
    ?>

    <h2>Код шаблона: <?=$arParams['FIELDS']['CODE']?></h2>
    <?=PHP_EOL?>

    <?php
}

if (!empty($arParams)):

    $arBp = [
        'CONFIG' => $arParams['CONFIG'],
        'FIELDS' => $arParams['FIELDS'],
        'POST' => $arParams['POST'],
    ];

    echo '<textarea style="width: 1200px; height: 600px">';

    echo 'public const PARAMS => [' . PHP_EOL;
    echoArray($arBp, 1);
    echo '];' . PHP_EOL;

    echo '</textarea>';

endif;
