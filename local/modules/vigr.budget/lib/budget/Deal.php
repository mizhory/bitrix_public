<?php
namespace Vigr\Budget;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Class Deal
 * @package Vigr\Budget
 */
class Deal
{
    /**
     * @var Budget
     */
    protected $budgetObject;

    /**
     * @var mixed
     */
    protected $productsIblock;

    /**
     * Deal constructor.
     */
    public function __construct()
    {
        \CModule::includeModule('crm');
        $this->productsIblock = getIblockIdByCode('deals_be');
        $this->budgetObject = new Budget(false);
    }

    /**
     * @param $arData
     * @param $needBudget
     * @return array
     * создает элементы УС для поля БЕ
     */
    public function createDealsBe($arData, $needBudget)
    {
        $arIds = [];

        $arFilter = [];

        if ($arData['ID'] > 0) {
            $dbItems = \CIBlockElement::GetList(
                [],
                [
                    [],
                    [
                        'PROPERTY_SDELKA_ZAYAVKA' => $arData['ID'],
                        'PROPERTY_PRODUKT_BE' => false,
                        'IBLOCK_ID' => getIblockIdByCode('deals_be')
                    ]
                ],
                false,
                false,
                [
                    'ID',
                ]
            );
            while ($arItem = $dbItems->fetch()) {
                \CIBlockElement::Delete($arItem['ID']);
            }
        }

        $arItems = [];

        $arData['sum'] = str_replace(' ', '', $arData['sum']);

        foreach ($arData['items'] as $beId => $arItem) {
            $arItem['sum'] = str_replace(' ', '', $arItem['sum']);
            if ($arItem['id'] > 1) {
                $itemHash = md5($beId . $arData['article'] . returnNameMonth($arData['month']) . $arData['year']);
                if ($arItem['items']) {
                    $arIds = array_merge($arIds,
                        $this->createItemForDealProduct($arItem, $itemHash, $beId, $arData['rateCurse']));
                }
                $arIds[] = $this->createItemForDeal($arItem, $itemHash, $beId, $arData['rateCurse'], $needBudget);
                $arFilter[] = [
                    'year' => date('Y'),
                    'article' => $arData['article'],
                    'biznesUnit' => $beId
                ];
            }
        }

        if (count($arFilter) === 1) {
            $arFilter = $arFilter[0];
        } else {
            $arFilter['LOGIC'] = ['OR'];
        }

        if($needBudget){
            $this->budgetObject->work('recalculate',$arFilter);
        }

        return $arIds;
    }


    /**
     * @param $arFilter
     * @param $arForUpdate
     * обновляет элементы УС сделки
     */
    public function updateDealsBe($arFilter, $arForUpdate)
    {
        $arFilter['IBLOCK_ID'] = getIblockIdByCode('deals_be');

        $dbDealItems = \CIBlockElement::GetList(
            [],
            $arFilter
        );

        $el = new \CIBlockElement;

        while ($arDealItem = $dbDealItems->fetch()) {
            $PROP = $arForUpdate;
            \CIBlockElement::SetPropertyValuesEx($arDealItem['ID'], false, $PROP);
        }
    }

    /**
     * @param $arItem
     * @param $itemHash
     * @param $beId
     * @param $rateCurse
     * @return array
     * создает продукты для БЕ
     */
    public function createItemForDealProduct($arItem, $itemHash, $beId, $rateCurse)
    {
        $arIds = [];
        $el = new \CIBlockElement;
        foreach ($arItem['items'] as $arProduct) {
            $arProps = [
                'BE_LIST' => $arItem['id'],
                'PSNT_BE' => $arItem['percent'],
                'SUMMA' => $arItem['sum'],
                'SUMMA_V_REZERVE' => round($arItem['sum'] / $rateCurse, 2),
                'OPLACHENO' => 0,
                'OPLACHENO_PO_VALYUTE' => 0,
                'PRODUKT_BE' => $arProduct['id'],
                'PSNT_PO_PRODUKTU' => $arProduct['percent'],
                'SUMMA_PO_PRODUKTU' => $arProduct['sum']
            ];

            $arLoadProductArray = [
                "MODIFIED_BY" => 1, // элемент изменен текущим пользователем
                "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
                "IBLOCK_ID" => $this->productsIblock,
                "PROPERTY_VALUES" => $arProps,
                "NAME" => time(),
                "ACTIVE" => "Y"            // активен
            ];

            $arIds[] = $el->Add($arLoadProductArray);
        }

        return $arIds;
    }

    /**
     * @param $dealId
     * @param false $needProduct
     * @return array
     * отдает элементы УС привязанные к БЕ
     */
    public function getDealProducts($dealId,$needProduct = false)
    {
        $arFilter = [
            'IBLOCK_ID'=>getIblockIdByCode('deals_be'),
            'PROPERTY_SDELKA_ZAYAVKA' => $dealId
        ];
        if(!$needProduct){
            $arFilter['PROPERTY_PRODUKT_BE'] = false;
        }
        $dbItems = \CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'IBLOCK_ID',
                'ID',
                'PROPERTY_OPLACHENO',
                'PROPERTY_OPLACHENO_PO_VALYUTE',
                'PROPERTY_PSNT_BE',
                'PROPERTY_SUMMA',
                'PROPERTY_BE_LIST',
                'PROPERTY_PRODUKT_BE',
                'PROPERTY_SUMMA_V_REZERVE',
            ]
        );

        $arItems = [];

        while ($arItem = $dbItems->fetch()) {
            $arItems[$arItem['ID']] = [
                'payInValute' => $arItem['PROPERTY_OPLACHENO_PO_VALYUTE_VALUE'],
                'payInCurse' => $arItem['PROPERTY_OPLACHENO_VALUE'],
                'product' => $arItem['PROPERTY_PRODUKT_BE_VALUE'],
                'percent' => $arItem['PROPERTY_PSNT_BE_VALUE'],
                'summa' => $arItem['PROPERTY_SUMMA_VALUE'],
                'summaInReserve' => $arItem['PROPERTY_SUMMA_V_REZERVE_VALUE'],
                'be' => $arItem['PROPERTY_BE_LIST_VALUE'],
                'listId'=>$arItem['ID']
            ];
        }

        return $arItems;
    }

    /**
     * @param $dealId
     * @param $arFields
     * @return array
     * отдает поля БЕ в заявке
     */
    public function getFieldsByDealBudget($dealId, $arFields)
    {
        $arDeal = \CCrmDeal::getList(['ID' => 'desc'], ['ID' => $dealId], ['UF_CRM_BIZUINIT_WIDGET_2'])->fetch();

        $arData = returnValideJson($arDeal['UF_CRM_BIZUINIT_WIDGET_2']);

        $arForReturn = [];

        foreach ($arFields as $field) {
            $arForReturn[$field] = $arData[$field];
        }

        return $arForReturn;
    }

    /**
     * @param $arItem
     * @param $hash
     * @param $be
     * @param $rateCurse
     * курс валюты
     * @param bool $needBudget
     * @return mixed
     * @throws \Exception
     * создает элемент УС с БЕ
     */
    public function createItemForDeal($arItem, $hash, $be, $rateCurse, $needBudget = true)
    {
        $el = new \CIBlockElement;

        $arProps = [
            'BE_LIST' => $arItem['id'],
            'PSNT_BE' => $arItem['percent'],
            'SUMMA' => $arItem['sum'],
            'SUMMA_V_REZERVE' => 0,
            'OPLACHENO' => 0,
            'OPLACHENO_PO_VALYUTE' => 0
        ];

        if ($needBudget) {
            $arProps['SUMMA_V_REZERVE'] = round($arItem['sum'] / $rateCurse, 2);
        }

        $arLoadProductArray = [
            "MODIFIED_BY" => 1, // элемент изменен текущим пользователем
            "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID" => $this->productsIblock,
            "PROPERTY_VALUES" => $arProps,
            "NAME" => time(),
            "ACTIVE" => "Y",            // активен
            "PREVIEW_TEXT" => "текст для списка элементов",
            "DETAIL_TEXT" => "текст для детального просмотра"
        ];


        if ($id = $el->Add($arLoadProductArray)) {
            if ($needBudget) {
                $this->budgetObject->work('workWithReserve', [
                    'sum' => $arProps['SUMMA_V_REZERVE'],
                    'hash' => $hash
                ]);
            }
        }

        return $id;
    }

}

















