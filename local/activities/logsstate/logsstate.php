<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Immo\Tools\CommonActivity;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CBPLogsState extends CBPActivity
{
    use CommonActivity;

    /**
     * @var \Bitrix\Main\ORM\Data\DataManager|string
     */
    private static $hlClass;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'ELEMENT_ID' => '',
            'IBLOCK_ID' => '',
            'WORKFLOW_ID' => '',
            'STAGE_TITLE' => '',
            'ACTION_TITLE' => '',
            'USER_ID' => '',
            'COMMENT' => '',
            'DATE_CREATE' => '',
        ];
        $this->SetPropertiesTypes([
            'USER_ID' => ['Type' => 'user']
        ]);
        Bitrix\Main\Loader::includeModule('highloadblock');
    }

    public function Execute(): int
    {
        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();
        $arLogFields = [
            'UF_ELEMENT_ID' => $this->ELEMENT_ID,
            'UF_IBLOCK_ID' => $this->IBLOCK_ID,
            'UF_WORKFLOW_ID' => $this->WORKFLOW_ID?:$rootActivity->getWorkflowInstanceId(),
            'UF_STAGE_TITLE' => $this->STAGE_TITLE,
            'UF_ACTION_TITLE' => $this->ACTION_TITLE,
            'UF_COMMENT' => $this->COMMENT,
            'UF_DATE_CREATE' => $this->DATE_CREATE,
        ];
        $arUsersTmp = $this->USER_ID;
        if (!is_array($arUsersTmp)) {
            $arUsersTmp = [$arUsersTmp];
        }
        $arUsers = CBPHelper::ExtractUsers($arUsersTmp, $documentId, false);
        $arLogFields['UF_USER_ID'] = $arUsers;
        $arLogFields['UF_USER_ID'] = reset($arLogFields['UF_USER_ID']);

        try {
            $sClassActivityLoggingTable = self::initHighloadClass();
            $obResult = $sClassActivityLoggingTable::add($arLogFields);
            if ($obResult->isSuccess()) {
                $this->WriteToTrackingService(
                    'Создан лог ID=' . $obResult->getId()
                );
            } else {
                $this->WriteToTrackingService(
                    'Ошибка ' . implode('|', $obResult->getErrorMessages())
                );
            }
        } catch (Exception $exception) {
            $this->WriteToTrackingService(
                'Exception ' . $exception->getMessage()
            );
        }
        return CBPActivityExecutionStatus::Closed;
    }

    public static function ValidateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null): array
    {
        $arErrors = [];
        if ($arTestProperties["ELEMENT_ID"] == '') {
            $arErrors[] = [
                'code' => 'empty_' . 'ELEMENT_ID',
                'message' => 'Пустое поле "ID элемента"',
            ];
        }
        if ($arTestProperties["IBLOCK_ID"] == '') {
            $arErrors[] = [
                'code' => 'empty_' . 'IBLOCK_ID',
                'message' => 'Пустое поле "ID информационного блока"',
            ];
        }
        if ($arTestProperties["ACTION_TITLE"] == '') {
            $arErrors[] = [
                'code' => 'empty_' . 'ACTION_TITLE',
                'message' => 'Пустое поле "Действие"',
            ];
        }
        if (!array_key_exists("USER_ID", $arTestProperties)) {
            $bUsersFieldEmpty = true;
        } else {
            if (!is_array($arTestProperties["USER_ID"])) {
                $arTestProperties["USER_ID"] = [$arTestProperties["USER_ID"]];
            }
            $bUsersFieldEmpty = true;
            foreach ($arTestProperties["USER_ID"] as $userId) {
                if (!is_array($userId) && (trim($userId) <> '') || is_array($userId) && (count($userId) > 0)) {
                    $bUsersFieldEmpty = false;
                    break;
                }
            }
        }
        if ($bUsersFieldEmpty) {
            $arErrors[] = ["code" => "NotExist", "parameter" => "Users", "message" => GetMessage("BPAR_ACT_PROP_EMPTY1")];
        }
        return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
    }

    public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors): bool
    {
        $arErrors = [];
        $arProperties = [
            "ELEMENT_ID" => $arCurrentValues["ELEMENT_ID"],
            "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"],
            "WORKFLOW_ID" => $arCurrentValues["WORKFLOW_ID"],
            "STAGE_TITLE" => $arCurrentValues["STAGE_TITLE"],
            "ACTION_TITLE" => $arCurrentValues["ACTION_TITLE"],
            "COMMENT" => $arCurrentValues["COMMENT"],
            "DATE_CREATE" => $arCurrentValues["DATE_CREATE"],
        ];
        $arProperties["USER_ID"] = CBPHelper::UsersStringToArray($arCurrentValues["USER_ID"], $documentType, $arErrors);
        $arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
        if (count($arErrors) > 0) {
            return false;
        }
        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        $arCurrentActivity["Properties"] = $arProperties;
        return true;
    }

    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
    {
        $arCurrentValues = (empty($arCurrentValues)) ? [] : $arCurrentValues;
        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        if (is_array($arCurrentActivity['Properties'])) {
            $arCurrentValues = array_merge($arCurrentValues, $arCurrentActivity['Properties']);
        }
        $arCurrentValues['USER_ID'] = CBPHelper::UsersArrayToString(
            $arCurrentActivity["Properties"]['USER_ID'],
            $arWorkflowTemplate,
            $documentType
        );
        return CBPRuntime::GetRuntime()->ExecuteResourceFile(
            static::getActivityFilePath(),
            "properties_dialog.php",
            [
                "arCurrentValues" => $arCurrentValues
            ]
        );
    }

    protected static function initHighloadClass()
    {
        if (self::$hlClass === null) {
            self::$hlClass = HighloadBlockTable::compileEntity('ActivityLogging')->getDataClass();
        }
        return self::$hlClass;
    }
}
