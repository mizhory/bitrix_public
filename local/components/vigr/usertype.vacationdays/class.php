<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

class CBVacationDays extends CBitrixComponent implements Controllerable
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

    public function buildDataByEdit()
    {

    }

    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'getDays' => [
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

    public function getDaysAction(){
        $context = Bitrix\Main\Application::getInstance()->getContext();
        $arValues = $context->getRequest()->getValues();

        switch ($arValues['mode']){
            case 'interval':
                $result = \Vigr\Helpers\DateHelper::getVacationDays($arValues['start'],$arValues['end']);
                break;
            case 'start':
                $result = \Vigr\Helpers\DateHelper::getVacationDate($arValues['start'],$arValues['days'])->format('d.m.Y');
                break;
            case 'end':
                $result = \Vigr\Helpers\DateHelper::getVacationDate($arValues['start'],$arValues['days'],1)->format('d.m.Y');
                break;
        }

        return $result;

    }

    public function buildDataByView()
    {
        $iblockId = getIblockIdByCode('departments');

        $arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$iblockId.'_SECTION',0,'ru');

        $arFields = [];

        foreach ($arUserFields as $key=>$arUserField){
            if($key == $this->arParams['value']['VALUE']){
                $this->arResult['name'] = $arUserField['EDIT_FORM_LABEL'];
            }
        }

    }

}