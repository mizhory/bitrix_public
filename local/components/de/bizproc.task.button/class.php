<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
class BizprocTaskButton extends CBitrixComponent {
    const AR_START_STAGE = ['C1:NEW'];
    const AR_AFTER_START_STAGE = ['C1:3'];

    const DOP_SOGLAS = 'UF_CRM_1614092827';
    const MAIN_SOGLAS = 'UF_CRM_1612781679';

    public function executeComponent() {
        $this->arResult['PROPS'] = $this->arParams['PROPS'];
        $this->arResult['GRID_ID'] = $this->arParams['GRID_ID'];
        $this->arResult['BUTTON_TEXT'] = $this->arParams['BUTTON_TEXT'];
        $this->arResult['TARGET_USER_ID'] = $this->arParams['TARGET_USER_ID'];
        $this->arResult['ENTITY_ID'] = $this->arParams['ENTITY_ID'];
        $this->arResult['USER_ID'] = $this->arParams['USER_ID'];

        $this->arResult['START_BUTTONS'] = $this->getStartButton($this->arParams['ENTITY_ID']);
        $this->arResult['DOP_SOGLAS_BUTTONS'] = $this->getDopSoglasBtn($this->arParams['ENTITY_ID']);

        //print_de($this->getDealData($this->arParams['ENTITY_ID'])); die();

        $this->arResult['AR_TASKS'] = $this->getUserTasks($this->arResult['USER_ID'], $this->arParams['ENTITY_ID']);

        //print_de($this->arResult['AR_TASKS']);
        $this->includeComponentTemplate();
    }

    protected function getDealData($dealId){
        $arFilter = ['ID' => $dealId, 'CHECK_PERMISSIONS' => 'N'];
        $arSelect = ['ID', 'STAGE_ID', 'ASSIGNED_BY_ID', 'CATEGORY_ID', self::DOP_SOGLAS, self::MAIN_SOGLAS];
        $res = \CCrmDeal::getListEx([], $arFilter, false, false, $arSelect)->fetch();
        return $res;
    }

    protected function getDopSoglasBtn($entityId){
        $dealData = $this->getDealData($entityId);

        global $USER;
        $userId = $USER->getId();

        $button = [];

        if(isset($dealData[self::DOP_SOGLAS]) && !empty($dealData[self::DOP_SOGLAS])){
            $arSogl = array_merge($dealData[self::MAIN_SOGLAS], [$dealData['ASSIGNED_BY_ID']]);
            $arStageSogl = array_merge(self::AR_START_STAGE, self::AR_AFTER_START_STAGE);

            if(in_array($userId, $arSogl) && !in_array($dealData['STAGE_ID'], $arStageSogl)){
                $button = [
                    'NAME' => '',
                    'BUTTON_TEXT' => 'Отправить допсогласующим',
                    'ONCLICK' => 'bspStarter.startWorkflow('.$entityId.', 48);'
                ];
            }
        }
        return $button;
    }

    protected function getStartButton($entityId){
        $dealData = $this->getDealData($entityId);

        $arButtons = [];

        global $USER;
        $userId = $USER->getId();

        if(isset($dealData['STAGE_ID']) && in_array($dealData['STAGE_ID'], self::AR_START_STAGE)
                && $userId == $dealData['ASSIGNED_BY_ID']){
            $arButtons = [
                [
                    'NAME' => '',
                    'BUTTON_TEXT' => 'отправить на портал',
                    'ONCLICK' => 'bspStarter.startWorkflow('.$entityId.', 43);'
                ],
                [
                    'NAME' => '',
                    'BUTTON_TEXT' => 'в черновик',
                    'ONCLICK' => 'bspStarter.startWorkflow('.$entityId.', 44);'
                ],
            ];

        }
        return $arButtons;
    }

    protected function getUserTasks($userId, $entityId){

        $arFilter = [
            'ENTITY' => 'CCrmDocumentDeal',
            'DOCUMENT_ID' => 'DEAL_'.$entityId,
            'USER_ID' => $userId,
            'USER_STATUS' => 0,
        ];
        $arSelectFields = ['*'];

        $dbRecordsList = \CBPTaskService::GetList(
            [],
            $arFilter,
            false,
            false,
            $arSelectFields
        );

        $arTaskBp = [];

        while ($arRecord = $dbRecordsList->getNext())
        {
            $arTaskBp[] = $arRecord;
        }

        return $arTaskBp;
    }
}