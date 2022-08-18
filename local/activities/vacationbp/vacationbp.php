<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPVacationBp
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'elId' => ''
        ];

        $this->SetPropertiesTypes([
            'countDays' => ['Type' => 'string']
        ]);
    }

    public function Execute()
    {
        $arRes = [];

        $elID = $this->elId;

        $arEl = CIBlockELement::getList(
            [],
            [
                'ID'=>$elID
            ],
            false,
            false,
            [
                'PROPERTY_ID_BP'
            ]
        )->fetch()['PROPERTY_ID_BP_VALUE'];

        if(!$arEl['TEXT']){
            $arValues = [];
        }else{
            $arValues = explode(',',$arEl['TEXT']);
        }

        $arValues[] = $this->workflow->GetInstanceId();

        $el = new CIBlockElement;


        CIBlockElement::SetPropertyValuesEx($elID, false,['ID_BP'=>implode(',',$arValues)]);

        return CBPActivityExecutionStatus::Closed;
    }

    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
    {
        if(!$arCurrentValues){
            $arCurrentValues = [];
        }
        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
            $arWorkflowTemplate, $activityName);
        if (is_array($arCurrentActivity['Properties'])) {
            $arCurrentValues = array_merge($arCurrentValues,
                $arCurrentActivity['Properties']);
        }

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
            'elId' => $arCurrentValues['elId']
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