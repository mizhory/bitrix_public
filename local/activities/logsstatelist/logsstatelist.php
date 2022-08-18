<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\UserTable;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Immo\Tools\CommonActivity;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CBPLogsStateList extends CBPActivity
{
    use CommonActivity;

    private static $sLine = '#DATE_CREATE# - #STAGE_TITLE# | #ACTION_TITLE# | #USER_NAME# #USER_SECOND_NAME# #USER_LAST_NAME# - #COMMENT#';

    /**
     * @var \Bitrix\Main\ORM\Data\DataManager|string
     */
    private static $hlClass;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'ELEMENT_ID' => '',
            'LogsStateList' => '',
            'SEPARATOR' => '\\n',
            'LINE' => '',
        ];
        $this->SetPropertiesTypes([
            'USER_ID' => ['Type' => 'user'],
            'LogsStateList' => ['Type' => 'string'],
            'SEPARATOR' => ['Type' => 'string'],
            'LINE' => ['Type' => 'string'],
        ]);
        Bitrix\Main\Loader::includeModule('highloadblock');
    }

    public function Execute(): int
    {
        $arFilter = [
            '=UF_ELEMENT_ID' => (int)$this->ELEMENT_ID,
        ];
        try {
            // Выбор записей в логе
            $sClassActivityLoggingTable = self::initHighloadClass();
            $arSelect = [
                'STAGE_TITLE' => 'UF_STAGE_TITLE',
                'ACTION_TITLE' => 'UF_ACTION_TITLE',
                'COMMENT' => 'UF_COMMENT',
                'DATE_CREATE' => 'UF_DATE_CREATE',
                'USER_ID' => 'USER.ID',
                'USER_NAME' => 'USER.NAME',
                'USER_SECOND_NAME' => 'USER.SECOND_NAME',
                'USER_LAST_NAME' => 'USER.LAST_NAME',
            ];
            $resActivityLogging = $sClassActivityLoggingTable::getList([
                'filter' => $arFilter,
                'select' => array_merge($arSelect, ['ID']),
                'runtime' => [
                    new ReferenceField(
                        'USER',
                        UserTable::class,
                        ['this.UF_USER_ID' => 'ref.ID'],
                        ['join_type' => Join::TYPE_LEFT]
                    )
                ]
            ]);
            $arResult = [];
            // шаблон для строки
            $sLine = $this->LINE;
            if (!$sLine) {
                $sLine = self::$sLine;
            }
            // пресеты создаются
            $arKeys = array_keys($arSelect);
            array_walk($arKeys, fn(&$sKey) => $sKey = '#' . $sKey . '#');
            while ($arActivityLogging = $resActivityLogging->fetch()) {
                // преобразование строки из шаблона в текст
                $arResult[] = str_replace($arKeys, $arActivityLogging, $sLine);
            }
            // разделитель
            $sSep = $this->SEPARATOR;
            $sSep = str_replace('\n', "\n", $sSep);
            if (!$sSep) {
                $sSep = PHP_EOL;
            }
            $sResult = implode($sSep, $arResult);
            //запись результата
            $this->LogsStateList = $sResult;
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
        return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
    }

    public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors): bool
    {
        $arErrors = [];
        $arProperties = [
            "ELEMENT_ID" => $arCurrentValues["ELEMENT_ID"],
            "SEPARATOR" => $arCurrentValues["SEPARATOR"],
            "LINE" => $arCurrentValues["LINE"],
        ];

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
        // занчения по умолчанию
        if (!$arCurrentValues['SEPARATOR']) {
            $arCurrentValues['SEPARATOR'] = '\\n';
        }
        if (!$arCurrentValues['LINE']) {
            $arCurrentValues['LINE'] = self::$sLine;
        }
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
