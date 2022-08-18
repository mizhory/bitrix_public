<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Immo\Iblock\Manager;

$iblockId = $arResult['jsParams']['iblockId'];

$arProperties = Manager::loadProperties(
    \Bitrix\Iblock\ORM\Query::filter()
        ->whereIn('CODE', [
            Manager::PROPERTY_CODE_DRAFT,
            Manager::PROPERTY_CODE_NAME,
            Manager::PROPERTY_CODE_TEMPLATE,
            Manager::PROPERTY_CODE_STATUS_CARD,
            'ASSIGNED_BY',
            'TO_USERS',
            'MONTH',
            'YEAR_LIST',
            'DONE_USERS',
            'ACCEPT_PLAN',
            'FILES',
            'SELECTED_PAYER',
            'PAYER_NAL',
            'DATE_CREATE_CARD'
        ])
        ->where('IBLOCK_ID', $iblockId),
    ['ID', 'CODE', 'NAME', 'LINK_IBLOCK_ID']
);

foreach ($arProperties as $id => $prop) {
    $arProps[$prop['CODE']] = $prop;
}

if (!empty($arProps)) {
    $arResult['jsParams']['select'] = [
        'form' => [
            'new' => "form#form_lists_element_add_{$iblockId}",
            'edit' => "form#form_lists_element_edit_{$iblockId}"
        ],
        'saveBtn' => [
            'tag' => 'input',
            'attribute'=> [
                'name' => 'save',
                'type' => 'submit'
            ]
        ],
        'props' => [
            'draft' =>  [
                'tag' => 'select',
                'attribute' => ['name' => "PROPERTY_{$arProps[Manager::PROPERTY_CODE_DRAFT]['ID']}"]
            ],
            'template' => [
                'new' => [
                    'tag' => 'input',
                    'attribute' => ['id' => 'template-card-checkbox']
                ],
                'edit' => "input[name^=PROPERTY_{$arProps[Manager::PROPERTY_CODE_TEMPLATE]['ID']}]"
            ],
            'toUsers' => [
                'tag' => 'input',
                'attribute' => ['name' => "PROPERTY_{$arProps['TO_USERS']['ID']}[]"]
            ],
            'appUsers' => [
                'tag' => 'input',
                'attribute' => ['name' => "PROPERTY_{$arProps['DONE_USERS']['ID']}[]"]
            ],
            'previewText' => [
                'tag' => 'input',
                'attribute' => ['name' => "PREVIEW_TEXT"]
            ],
            'status' => 'span[id^="status-card-"]',
            'plan' => "input[name^=PROPERTY_{$arProps['ACCEPT_PLAN']['ID']}]",
            'month' => "select[name^=PROPERTY_{$arProps['MONTH']['ID']}]",
            'year' => "select[name^=PROPERTY_{$arProps['YEAR_LIST']['ID']}]",
            'dateCreateCard' => "input[name^=PROPERTY_{$arProps['DATE_CREATE_CARD']['ID']}]"
        ],
        'name' => 'input[name=NAME]',
        'popup' => '.workarea-content-paddings',
        'assignedBy' => [
            'selector' => "input[name^=PROPERTY_{$arProps['ASSIGNED_BY']['ID']}]",
            'originValue' => \Immo\Tools\User::getCurrent()->getId(),
            'checkProps' => ['draft'],
            'text' => 'Отправить на согласование ответственному'
        ]
    ];

    $arResult['jsParams']['triggerProps'] = [
        'draft' => [
            'value' => Manager::getEnumByCode($arProps[Manager::PROPERTY_CODE_DRAFT]['ID'], 'Y')['ID'],
            'text' => 'Сохранить как черновик'
        ],
        'template' => [
            'value' => \Immo\Iblock\Property\TemplateCard::IS_TEMPLATE_VALUE,
            'text' => 'Сохранить как шаблон'
        ],
    ];

    $arResult['jsParams']['hideProps'] = [
        'toUsers',
        'appUsers',
        'previewText',
        'plan',
        'status'
    ];

    $arResult['jsParams']['employeeType'] = [
        'toUsers',
        'appUsers',
    ];

    if (!empty($arProps['MONTH']) and !empty($arProps['YEAR_LIST'])) {
        foreach (Manager::getEnums($arProps['MONTH']['ID']) as $enum) {
            $arMonth[$enum['XML_ID']] = $enum['ID'];
        }

        $rsYears = \Bitrix\Iblock\ElementTable::query()
            ->where('IBLOCK_ID', $arProps['YEAR_LIST']['LINK_IBLOCK_ID'])
            ->setSelect(['NAME', 'ID'])
            ->exec();
        while ($year = $rsYears->fetch()) {
            $arYears[$year['NAME']] = $year['ID'];
        }

        $isFd = (\Immo\Tools\User::getCurrent()->isUserFd() === true) ? 'N' : 'Y';
        $currentDate = new \Bitrix\Main\Type\Date();

        $year = \Immo\Iblock\Property\BiznesUnitsIblockField::defineFinancialYear() ?? $currentDate->format('Y');
        $arResult['jsParams']['ajaxUpdateProps'] = [
            [
                'enums' => $arMonth ?? [],
                'propertyCode' => $arProps['MONTH']['CODE'],
                'prop' => 'month',
                'defaultValue' => $arMonth[$currentDate->format('M')] ?? '',
                'disable' => $isFd
            ],
            [
                'enums' => $arYears ?? [],
                'propertyCode' => $arProps['YEAR_LIST']['CODE'],
                'prop' => 'year',
                'defaultValue' => $arYears[$year] ?? '',
                'disable' => $isFd
            ],
        ];
    }

    if (!empty($arProps['FILES'])) {
        $arResult['jsParams']['select']['files'] = [
            'selector' => "input[name^=PROPERTY_{$arProps['FILES']['ID']}]",
            'multiple' => 'Y',
            'id' => "PROPERTY_{$arProps['FILES']['ID']}"
        ];
        $arResult['jsParams']['ajaxUpdateProps'][] = [
            'propertyCode' => $arProps['FILES']['CODE'],
            'prop' => 'files',
            'multiple' => 'Y',
        ];
    }
}

/**
 * Получение ID текущего элемента
 */
if (
    !empty($sefFolder = \Bitrix\Main\Config\Option::get('lists', 'livefeed_url', ''))
    and !empty($arParams['IBLOCK_FIELDS']['DETAIL_PAGE_URL'])
) {
    $arParams['IBLOCK_FIELDS']['DETAIL_PAGE_URL'] = str_replace(
        $sefFolder,
        '',
        $arParams['IBLOCK_FIELDS']['DETAIL_PAGE_URL']
    );
    if (!empty($arParams['IBLOCK_FIELDS']['DETAIL_PAGE_URL'])) {
        $arVariables = [];

        $componentPage = CComponentEngine::ParseComponentPath($sefFolder, [
            "detailPage" => $arParams['IBLOCK_FIELDS']['DETAIL_PAGE_URL'],
        ], $arVariables);

        if (!empty($componentPage)) {
            CComponentEngine::InitComponentVariables($componentPage, [], [], $arVariables);

            $arResult['jsParams']['elementParams'] = [
                'id' => $arVariables['ID'],
                'iblockId' => $iblockId,
                'listUrl' => $arResult['LIST_URL']
            ];
        }
    }
}

$arResult['jsParams']['removeSelectEmptyValue'] = 'Y';
if (!empty($arProps['SELECTED_PAYER'])) {
    $arResult['jsParams']['notRemoveEmptyValue'] = [
        "PROPERTY_{$arProps['SELECTED_PAYER']['ID']}"
    ];
}
if (!empty($arProps['PAYER_NAL'])) {
    $arResult['jsParams']['notRemoveEmptyValue'][] = "PROPERTY_{$arProps['PAYER_NAL']['ID']}";
}
