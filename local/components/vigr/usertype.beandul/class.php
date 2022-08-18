<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;


class CBRoles extends CBitrixComponent implements Controllerable
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
            'getData' => [
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

    public function getDataAction(){
        $context = Bitrix\Main\Application::getInstance()->getContext();
        $arValues = $context->getRequest()->getValues();

        $type = $arValues['type'];

        $arResult = ['DATA'=>\Vigr\Helpers\Helper::getElementsByIb(
            [
                'IBLOCK_ID'=>getIblockIdByCode($type)
            ],
            [
                'ID',
                'NAME'
            ]
        )];
        ob_start();
        include 'templates/edit/selectIbs.php';
        return ob_get_clean();
    }

    public function buildDataByEdit()
    {
        $this->arResult['PARAMS'] = json_decode(html_entity_decode(stripslashes($this->arParams['userField']['VALUE'])), 1);

        $type = $this->arResult['PARAMS']['type'] ?? 'be';

        $this->arResult['DATA'] = \Vigr\Helpers\Helper::getElementsByIb(
            [
                'IBLOCK_ID'=>getIblockIdByCode($type)
            ],
            [
                'ID',
                'NAME'
            ]
        );
    }

    public function buildDataByView()
    {

    }

}