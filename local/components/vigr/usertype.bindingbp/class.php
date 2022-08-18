<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

class CBBindingBp extends CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        switch ($this->arParams['page']) {
            case 'edit':
                $this->buildDataByEdit();
                break;
            case 'view':
                $this->buildDataByView();
                break;
        }
        $this->IncludeComponentTemplate();
    }

    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'getIbs' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }

    public function buildDataByEdit()
    {
        $dbIblockTypes = \CIBlockType::getList();

        $arData = [
            'TYPES' => [],
            'IBLOCKS' => []
        ];

        while ($iblockType = $dbIblockTypes->fetch()) {
            $arData['TYPES'][] = [
                'ID' => $iblockType['ID'],
                'NAME' => CIBlockType::GetByIDLang($iblockType['ID'], 'ru')['NAME']
            ];
        }


        if (!$this->arParams['userField']['VALUE']) {
            $iblockType = $arData['TYPES'][0]['ID'];
        } else {
            $iblockType = CIBlock::GetList(
                [],
                [
                    'ID' => $this->arParams['userField']['VALUE']
                ]
            )->fetch()['IBLOCK_TYPE_ID'];
        }

        $dbIblocks = CIBlock::GetList(
            [],
            [
                'TYPE' => $iblockType
            ]
        );

        $arData['IB_TYPE'] = $iblockType;

        while ($arIblock = $dbIblocks->fetch()) {
            $arData['IBLOCKS'][] = [
                'ID' => $arIblock['ID'],
                'NAME' => $arIblock['NAME']
            ];
        }

        $this->arResult['DATA'] = $arData;
        $arValues = $this->arParams['userField'];
    }

    public function buildDataByView()
    {
        $iblockName = CIBlock::GetList(
            [],
            [
                'ID' => $this->arParams['value']
            ]
        )->fetch()['NAME'];

        $this->arResult['name'] = $iblockName;
    }

    public function getIbsAction()
    {
        $context = Bitrix\Main\Application::getInstance()->getContext();
        $arValues = $context->getRequest()->getValues();

        $dbIblocks = CIBlock::GetList(
            [],
            [
                'TYPE' => $arValues['iblockType']
            ]
        );

        while ($arIblock = $dbIblocks->fetch()) {
            $arData['IBLOCKS'][] = [
                'ID' => $arIblock['ID'],
                'NAME' => $arIblock['NAME']
            ];
        }
        $arResult = ['DATA' => $arData];

        ob_start();
        include "templates/selectIbs.php";
        $content = ob_get_clean();

        return $content;
    }
}