<?php

namespace Immo\Components;


use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Main\Loader;
use Immo\Tools\BizprocHelper;
use CBPDocumentEventType;
use CBPTaskUserStatus;
use Bitrix\Main;
use CBitrixComponent;
use CBPTaskResult;
use CBPTaskService;

/**
 * Class CustomBPAction
 * @package Immo\Components
 * @description Класс отвечающий за вывод формы задания бизнес-процессов, у которых текущий пользователь является
 * исполнителем
 */
class CustomBPAction extends CBitrixComponent
{
    protected bool $hasError = false;

    public function onPrepareComponentParams($arParams): array
    {
        $arParams['USER_ID'] = (int)$arParams['USER_ID'];
        $arParams['ELEMENT_ID'] = (int)$arParams['ELEMENT_ID'];
        $arParams['IBLOCK_ID'] = (int)$arParams['IBLOCK_ID'];

        if (!$arParams['USER_ID']) {
            $this->__showError('ID пользователя должно быть числом');
            $this->hasError = true;
        }

        if (!$arParams['ELEMENT_ID']) {
            $this->__showError('ID элемента должно быть числом');
            $this->hasError = true;
        }

        if (!$arParams['IBLOCK_ID']) {
            $this->__showError('ID инфоблока должно быть числом');
            $this->hasError = true;
        }

        if (!$arParams['IBLOCK_TYPE']) {
            $arParams['IBLOCK_TYPE'] = 'lists';
        }

        return $arParams;
    }

    /**
     * @description Возвращает список заданий
     * @return array
     * @throws Main\LoaderException
     */
    protected function getTaskItems(): array
    {
        Loader::includeModule('lists');

        $aResTask = [];
        $iblockId = $this->arParams['IBLOCK_ID'];
        $sIblockType = $this->arParams['IBLOCK_TYPE'];
        $documentId = $this->arParams['ELEMENT_ID'];
        $userId = $this->arParams['USER_ID'];
        $arDocument = \BizprocDocument::generateDocumentComplexType($sIblockType, $iblockId);
        $arFilter = [];
        [$arFilter['=MODULE_ID'], $arFilter['=ENTITY'], $arFilter['=DOCUMENT_TYPE']] = $arDocument;
        $sModule = $arFilter['=MODULE_ID'];



        if (!$this->arParams['~FILTER_BP_TEMPLATE']) {
            $arFilter['FILTER_BP_TEMPLATE'] = CBPDocumentEventType::Create;
        } elseif (is_array($this->arParams['~FILTER_BP_TEMPLATE'])) {
            $arFilter = array_merge($arFilter, $this->arParams['~FILTER_BP_TEMPLATE']);
        }
        $arWorkflowTemplateID = [];
        $resWfTemp = WorkflowTemplateTable::getList([
            'filter' => $arFilter,
            'select' => ['ID']
        ]);
        while ($arTemplate = $resWfTemp->fetch()){
            $arWorkflowTemplateID[] = $arTemplate['ID'];
        }
        if (!$arWorkflowTemplateID) {
            return [];
        }

        $oTasks = CBPTaskService::GetList(
            [],
            [
                'WORKFLOW_TEMPLATE_ID' => $arWorkflowTemplateID,
                'USER_ID' => $userId,
                'MODULE_ID' => $sModule,
                'DOCUMENT_ID' => $documentId,
                'USER_STATUS' => CBPTaskUserStatus::Waiting,
            ],
            false,
            false,
            [
                'ID',
                'USER_ID'
            ]
        );

        if ($oTasks instanceof CBPTaskResult) {
            while ($aTask = $oTasks->Fetch()) {
                $aResTask[] = $aTask;
            }
        }
        return $aResTask;
    }

    /**
     * @return mixed|void|null
     * @throws Main\LoaderException
     */
    public function executeComponent()
    {
        global $APPLICATION;
        if ($this->hasError) {
            return;
        }

        $this->arResult = $this->getTaskItems();

        $this->includeComponentTemplate();

        if ($this->arParams['NEED_LOCAL_REDIRECT'] === 'Y' && $this->request->getPost('action') === 'doTask') {
            LocalRedirect($APPLICATION->GetCurDir());
        }
    }
}
