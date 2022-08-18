<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetDealInfoActivity
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'dealId'=>'',
            'curse'=>'',
            'rate'=>'',
            'rateBe' => '',
        ];

        $this->SetPropertiesTypes([
            'arElements' => ['Type' => 'string']
        ]);
    }

    public function Execute()
    {
        $arRes = [];

        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();
        CModule::includeModule('vigr.budget');
        $deal = new \Vigr\Budget\Deal();
        $budget = new Vigr\Budget\Budget();
        //$arUsers = CBPHelper::ExtractUsers($this->user, $documentId);

        $arFields = $deal->getFieldsByDealBudget($this->dealId,['rate','rateBe','rateCurse']);

        /*
        if($arFields['rate'] === 'RUB' && $arFields['rateBe'] !== $arFields['rate']){
            $arFields['rateCurse'] = $budget->getRateByRub($arFields['rateBe']);
        }else
            */

        if($arFields['rate'] === $arFields['rateBe']){
            $arFields['rateCurse'] = 1;
        }

        $this->rate = $arFields['rate'];
        $this->rateBe = $arFields['rateBe'];
        $this->curse = $arFields['rateCurse'];


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
            'dealId' => $arCurrentValues['dealId'],
            'action' => $arCurrentValues['action'],
            'sum'=> $arCurrentValues['sum'],
            'reReserve' =>$arCurrentValues['reReserve'],
            'curse' =>$arCurrentValues['curse']
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