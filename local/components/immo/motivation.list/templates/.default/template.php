<?php

use Bitrix\Main;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
global $USER;
?><?php
$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass . " " : "") . "pagetitle-toolbar-field-view");
$this->SetViewTarget('inside_pagetitle'); ?>
    <div class="pagetitle-container pagetitle-flexible-space ">
        <?php
        $APPLICATION->IncludeComponent(
            'bitrix:main.ui.filter',
            '',
            [
                'FILTER_ID' => $arResult['FILTER_ID'],
                'GRID_ID' => $arResult['GRID_ID'],
                'FILTER' => $arResult['FILTER'],
                'ENABLE_LIVE_SEARCH' => true,
                'ENABLE_LABEL' => true,
//            'DISABLE_SEARCH' => false,
            ]
        );
        ?>
    </div>
    <div class="pagetitle-container pagetitle-align-right-container ">
        <a href="/sheets/motivation/?DOWNLOAD=Y" class="ui-btn ui-btn-light-border ui-btn-themes">Скачать</a>
        <?php
        if ($arParams['CAN_CREATE'] === 'Y') { ?>
            <a href="/sheets/motivation/0/" class="ui-btn ui-btn-success">Создать новую ведомость</a>
            <?php
        }
        ?>
    </div>
<?php
$this->EndViewTarget();
?><?php
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['COLUMNS'],
        'ROWS' => $arResult['MOTIVATION_LIST'],
        'SHOW_ROW_CHECKBOXES' => false,
        'DEFAULT_PAGE_SIZE' => $arResult['DEFAULT_PAGE_SIZE'],
        'NAV_OBJECT' => $arResult['NAV'],
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' => [
            ['NAME' => "5", 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100']
        ],
        'AJAX_OPTION_JUMP' => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU' => false,
        'SHOW_GRID_SETTINGS_MENU' => $USER->IsAdmin(),
        'SHOW_NAVIGATION_PANEL' => true,
        'SHOW_PAGINATION' => true,
        'SHOW_SELECTED_COUNTER' => false,
        'SHOW_TOTAL_COUNTER' => false,
        'SHOW_PAGESIZE' => true,
        'SHOW_ACTION_PANEL' => true,
        'ALLOW_COLUMNS_SORT' => true,
        'ALLOW_COLUMNS_RESIZE' => true,
        'ALLOW_HORIZONTAL_SCROLL' => true,
        'ALLOW_SORT' => true,
        'ALLOW_PIN_HEADER' => true,
        'AJAX_OPTION_HISTORY' => 'N'
    ]
);
?>