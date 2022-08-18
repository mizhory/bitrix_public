<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER, $APPLICATION;

$ajaxId = CAjax::getComponentID('bitrix:main.ui.grid', '.default', '');
$gridId = "grid_{$arResult['filterId']}";

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleFlexibleSpace = "lists-pagetitle-flexible-space";
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
    $bodyClass = $APPLICATION->GetPageProperty("BodyClass");
    $APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."pagetitle-toolbar-field-view");
    $this->SetViewTarget("inside_pagetitle");
    $pagetitleFlexibleSpace = "";
    $pagetitleAlignRightContainer = "";
}
elseif(!IsModuleInstalled("intranet"))
{
    $APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-flexible-space <?=$pagetitleFlexibleSpace?>">
    <? $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
        'FILTER_ID' => $arResult['filterId'],
        'GRID_ID' => $gridId,
        'FILTER' => $arResult['filterFields'],
        'ENABLE_LIVE_SEARCH' => false,
        'ENABLE_LABEL' => true
    ], null, ['HIDE_ICONS' => 'Y']
    ); ?>
</div>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
    <input type="hidden" value="<?= $arParams['type'] ?>" id='type'>
    <button class="ui-btn ui-btn-primary">
        <span class="ui-btn-text download">Выгрузить в excel</span>
    </button>
</div>
<?
if($isBitrix24Template)
{
    $this->EndViewTarget();
}

$filter = $arParams['filterData'];
if (empty($filter['budget'])) {
    $filter['budget'] = [];
}

array_unshift($filter['budget'], 'plan');

$arBudgetRows = array_merge($arParams['budgetRows']['HIDE'], $arParams['budgetRows']['VISIBLE']);
foreach ($arBudgetRows as $key => $name) {
    if (in_array($key, $filter['budget'])) {
        continue;
    }

    unset($arBudgetRows[$key]);
}

$arColumns = [
    [
        'id' => 'year',
        'name' => 'Фин Год',
        'default' => true,
        'resizeable' => false,
    ],
    [
        'id' => 'biznesUnit',
        'name' => 'ID БЕ',
        'default' => true,
        'resizeable' => false
    ],
    [
        'id' => 'BE_NAME',
        'name' => 'Название БЕ',
        'default' => true,
        'resizeable' => false
    ],
];

if ($arParams['NEED_RATE']) {
    $arColumns[] = [
        [
            'id' => 'rate',
            'name' => 'Валюта',
            'default' => true,
            'resizeable' => false
        ],
    ];
}

$arColumns[] = [
    'id' => 'article',
    'name' => 'Статья',
    'default' => true,
    'resizeable' => false
];
$arColumns[] = [
    'id' => 'budget',
    'name' => 'Бюджет',
    'default' => true,
    'resizeable' => false
];

$arMonths = \Immo\Integration\Budget\BudgetHelper::getMonths();

foreach ($arMonths as $monthNum => $monthName) {
    $arColumns[] = [
        'id' => "month_{$monthNum}",
        'name' => $monthName,
        'default' => true,
        'resizeable' => false,
        'width' => 118
    ];
}

$arColumns[] = [
    'id' => 'total',
    'name' => 'Итого',
    'default' => true,
    'resizeable' => false,
    'width' => 118
];

$arRows = [];
foreach ($arResult['data'] as $hash => $data) {
    $arData = [
        'id' => $hash,
        'data' => [],
        'columns' => [
            'year' => '<span class="year" data-year="' . $data['year'] . '">' . $data['year'] . '</span>',
            'biznesUnit' => $data['beId'],
            'BE_NAME' => $data['biznesUnit'],
            'article' => '<span ' . $data['articleData'] . ' class="' . $data['articleClass'] . '">' . $data['article'] . '</span>',
            'budget' => [],
            'total' => ''
        ],
        'columnClasses' => [
            'budget' => 'no-margin',
            'total' => 'no-margin',
        ],
    ];

    foreach ($arBudgetRows as $key => $nameRow) {
        foreach ($arMonths as $monthNum => $name) {
            $value = \Immo\Integration\Budget\BudgetHelper::formatNumber($data['data'][$monthNum][$key]);
            $arData['columns']["month_{$monthNum}"] .= '<span class="budget-cell">' . ($value ?? 0) . '</span>';
            $arData['columnClasses']["month_{$monthNum}"] = 'no-margin';
            if (!isset($value)) {
                continue;
            }

            if (!array_key_exists($key, $arData['columns']['budget'])) {
                $arData['columns']['budget'][$key] = '<span class="budget-cell">' . $nameRow . '</span>';
                $data[$key] = \Immo\Integration\Budget\BudgetHelper::formatNumber($data[$key]);
                $arData['columns']['total'] .= '<span class="budget-cell">' . ($data[$key] ?? 0) . '</span>';
            }
        }
    }

    if (!empty($arData['columns']['budget'])) {
        $arData['columns']['budget'] = implode('', $arData['columns']['budget']);
    }

    if ($arParams['NEED_RATE']) {
        $arData['columns']['rate'] = $arParams['rates'][$arData['beId']];
    }
    $arRows[] = $arData;
}

$APPLICATION->IncludeComponent(
    "bitrix:main.ui.grid",
    "",
    array(
        "GRID_ID" => $gridId,
        "COLUMNS" => $arColumns,
        "ROWS" => $arRows,
        "MESSAGES" => [],
        "NAV_STRING" => [],
        "TOTAL_ROWS_COUNT" => false,
        "PAGE_SIZES" => false,
        "AJAX_MODE" => "Y",
        "AJAX_ID" => $ajaxId,
        "ENABLE_NEXT_PAGE" => false,
        "ACTION_PANEL" => false,
        "AJAX_OPTION_JUMP" => "N",
        "SHOW_CHECK_ALL_CHECKBOXES" => true,
        "SHOW_ROW_CHECKBOXES" => false,
        "SHOW_ROW_ACTIONS_MENU" => false,
        "SHOW_GRID_SETTINGS_MENU" => false,
        "SHOW_NAVIGATION_PANEL" => false,
        "SHOW_PAGINATION" => false,
        "SHOW_SELECTED_COUNTER" => false,
        "SHOW_TOTAL_COUNTER" => false,
        "SHOW_PAGESIZE" => false,
        "SHOW_ACTION_PANEL" => false,
        "ALLOW_COLUMNS_SORT" => false,
        "ALLOW_COLUMNS_RESIZE" => false,
        "ALLOW_HORIZONTAL_SCROLL" => true,
        "ALLOW_SORT" => false,
        "ALLOW_PIN_HEADER" => true,
        "AJAX_OPTION_HISTORY" => "N"
    ),
    null, array("HIDE_ICONS" => "Y")
);
?>

<script type="text/javascript">

    BX(function () {
        const grid = BX.Main.gridManager.getById('<?=$gridId?>'),
            pageHead = document.querySelector('.page-header'),
            pageHeadParent = pageHead.parentNode;

        let parentPin = {}

        window.addEventListener('message', function (event) {
            var message = event.data;
            if (message === 'close') {
                BX.SidePanel.Instance.close();
            }
        });

        BX.addCustomEvent("SidePanel.Slider:onCloseComplete", () => {
            if (!!grid) {
                grid.instance.reload();
            }
        });

        BX.addCustomEvent('Grid::headerPinned', () => {
            if (!grid || !pageHead) {
                return;
            }

            grid.instance.pinHeader.container.insertBefore(pageHead, grid.instance.pinHeader.container.firstChild);
        })

        BX.addCustomEvent('Grid::headerUnpinned', () => {
            if (!grid || !pageHead) {
                return;
            }

            pageHeadParent.appendChild(pageHead)
        })
    });

</script>

