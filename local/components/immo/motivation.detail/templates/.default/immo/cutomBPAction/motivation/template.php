<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CMain $APPLICATION
 * @var string $templateFolder
 * @var array $arResult
 * @var array $arParams
 * @var Immo\Components\CustomBPAction $component
 */
global $APPLICATION;


foreach ($arResult as $aItem) {
    $APPLICATION->IncludeComponent(
        'bitrix:bizproc.task',
        '',
        [
            'TASK_ID' => $aItem['ID'],
            'USER_ID' => $arParams['USER_ID'],
            'TASK_EDIT_URL' => $APPLICATION->GetCurDir()
        ],
        $component->getParent()?:$component,
        [
            'HIDE_ICONS' => 'Y'
        ]
    );
}
