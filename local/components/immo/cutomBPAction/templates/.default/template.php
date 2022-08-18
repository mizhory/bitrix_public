<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CMain $APPLICATION
 * @var string $templateFolder
 * @var array $arResult
 * @var array $arParams
 * @var Immo\Components\CustomBPAction $component
 */

foreach ($arResult as $aItem) {
    $APPLICATION->IncludeComponent(
        'bitrix:bizproc.task',
        '',
        [
            'TASK_ID' => $aItem['ID'],
            'USER_ID' => $arParams['USER_ID'],
        ],
        $component->getParent(),
        [
            'HIDE_ICONS' => 'Y'
        ]
    );
}