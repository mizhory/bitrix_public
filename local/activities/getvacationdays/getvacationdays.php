<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetVacationDays
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'dateStart' => '',
            'dateEnd'=>'',
            'countDays'=>''
        ];

        $this->SetPropertiesTypes([
            'countDays' => ['Type' => 'string']
        ]);
    }

    public function Execute()
    {
        $arRes = [];

        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();

        $this->countDays = \Vigr\Helpers\DateHelper::getVacationDays($this->dateStart,$this->dateEnd);

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
            'dateStart' => $arCurrentValues['dateStart'],
            'dateEnd' => $arCurrentValues['dateEnd']
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