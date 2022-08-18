<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

class CBUlAndHeads extends CBitrixComponent implements Controllerable
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
        global $USER;
        $id = $USER->getId();

        $arUfs = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('USER', $id);

        $arUF = json_decode($arUfs['UF_CS_LEGAL']['VALUE'],
            1)['legalSelect'];
        $arUF = explode(',', $arUF);

        $arMainData = [
            'id' => $id,
            'name' => $USER->GetFullName(),
            'photo' => '',
            'poistion' => '',
            'sub' => false,
            'sup' => false
        ];

        $this->arResult['logData'] = false;

        if ($arUfs['UF_LOG_RUK']['VALUE']) {
            $arUser = CUser::GetByID($arUfs['UF_LOG_RUK']['VALUE'])->fetch();;
            $arLogData = ['name' => $arUser['NAME'] . ' ' . $arUser['LAST_NAME'], 'id' => $arUser['ID']];
        }

        $this->arResult['mainData'] = $arMainData;

        $this->arResult['logData'] = $arLogData;

        $this->arResult['names'] = $this->getDataByUserId($id);

        $this->arResult['user'] = $this->getAllUser();
    }

    public function getAllUser()
    {
        $arUsers = [];

        $dbUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"));

        while ($arUser = $dbUsers->fetch()) {
            $arUsers[$arUser['ID']] = $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
        }

        return $arUsers;
    }

    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'getUr' => [
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

    public function getUrAction()
    {
        $context = Bitrix\Main\Application::getInstance()->getContext();
        $arValues = $context->getRequest()->getValues();

        $arNames = $this->getDataByUserId($arValues['userId']);

        $arResult = [
            'user'=>$this->getAllUser(),
        ];

        ob_start();
        include 'templates/edit/ur.php';
        return ob_get_clean();
    }

    public function getDataByUserId($userId)
    {
        $arUfs = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('USER', $userId);

        $arUF = json_decode($arUfs['UF_CS_LEGAL']['VALUE'],
            1)['legalSelect'];


        $arNames = [];

        if ($arUF != '') {
            $arUF = explode(',', $arUF);
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();

            $hlDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

            $dbData = $hlDataClass::getList([
                'filter'=>[
                    'ID'=>$arUF
                ]
            ]);

            while($arData = $dbData->fetch()){
                $arIds[] = json_encode(['type'=>'companies','value'=>$arData['UF_COMPANY']]);
            }
            if(!empty($arIds)){
                $dbSection = CIBLockSection::getList(
                    [],
                    [
                        'IBLOCK_ID'=>getIblockIdByCode('departments'),
                        'UF_BE_AND_UL'=>$arIds
                    ],
                    false,
                    [
                        'ID',
                        'NAME'
                    ]
                );

                while($arSection = $dbSection->fetch()){
                    $arNames[$arSection['ID']] = $arSection['NAME'];
                }
            }



        }

        return $arNames;
    }

    public function buildDataByView()
    {


    }

}