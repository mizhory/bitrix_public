<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetUsersForStage
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'bp' => '',
            'stage' => '',
            'depId' => '',
            'userStr' => ''
        ];

        $this->SetPropertiesTypes([
            'userStr' => ['Type' => 'string']
        ]);
    }

    public function Execute()
    {
        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();

        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(3)->fetch();

        $hlDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

        $arData = $hlDataClass::getList([
            'filter'=>[
                'UF_ROUTE_STAGE'=>$this->stage,
                'UF_BP_IBLOCK_ID'=>$this->bp
            ]
        ])->fetch();

        $role = $arData['UF_ROLES'];
        global $USER_FIELD_MANAGER;

        $userFieldsList = $USER_FIELD_MANAGER->getUserFields("IBLOCK_3_SECTION", $this->depId, LANGUAGE_ID);
        $arUsers = [];
        foreach ($userFieldsList as $arValue){
            if($arValue['FIELD_NAME'] == $role){
                $arUsers = $arValue['VALUE'];
            }
        }


        if(is_array($arUsers)){
            $this->userStr=implode(',',$arUsers);
        }else{
            $this->userStr=$arUsers;
        }

        return CBPActivityExecutionStatus::Closed;
    }

    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
    {
        $dbIblocks = CIBlock::GetList(
            [],
            [
                'TYPE'=>'bitrix_processes'
            ]
        );

        $arIblocks = [];

        if(!$arCurrentValues){
            $arCurrentValues = [];
        }

        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
            $arWorkflowTemplate, $activityName);
        if (is_array($arCurrentActivity['Properties'])) {
            $arCurrentValues = array_merge($arCurrentValues,
                $arCurrentActivity['Properties']);
        }

        $bpId = 0;

        if($arCurrentValues['bp'] > 0){
            $bpId = $arCurrentValues['bp'];
        }

        while($arIblock = $dbIblocks->fetch()){
            $arIblocks[$arIblock['ID']] = $arIblock['NAME'];
        }

        $arCurrentValues['IBLOCKS'] = $arIblocks;
        $arCurrentValues['IB'] = $bpId;

        $arProps = [];
        $runtime = CBPRuntime::GetRuntime();
        return $runtime->ExecuteResourceFile(
            __FILE__,
            "properties_dialog.php",
            array(
                "arCurrentValues"=>$arCurrentValues
            )
        );
    }

    public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
    {
        $runtime = CBPRuntime::GetRuntime();
		
        $arProperties = array(
            'bp' => $arCurrentValues['bp'],
            'stage' => $arCurrentValues['stage'],
            'depId' => $arCurrentValues['depId'],
        );

        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
            $arWorkflowTemplate,
            $activityName
        );
        $arCurrentActivity['Properties'] = $arProperties;

        return true;
    }
    public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
    {
        $arErrors = array();


        return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
    }
}
?>