<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPBudgetActivity
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'action' => '',
            'user'=>'',
            'sum'=>'',
            'dealId'=>'',
            'curse'=>'',
            'reReserve'=>'',
            'status' => '',
            'errors' => ''
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


        //$arUsers = CBPHelper::ExtractUsers($this->user, $documentId);

        $arForStr = [];
		
		$arFilter = [];

		$type = $this->action;
		$sum = $this->sum;
        $curse = $this->curse;
        $reReserve = $this->reReserve;
        $dealIld = $this->dealId;

        if($type === 'PUY'){
            $type = 'PARTIAL_PAY';
        }

		$arData = [
		    'dealId'=>$dealIld,
            'value'=>$sum,
            'curse'=>$curse
        ];

		if($reReserve > 1 || $reReserve == 'Y'){
            $arData['reserve'] = 'Y';
        }

		CModule::includeModule('vigr.budget');

        try {
            $budget = new Vigr\Budget\Budget();

            $budget->work('workWithReserve',$arData,$type);

            $arDataProcessed = $budget->buildDataByActivity($arData);
            $budget->recalculate($arDataProcessed['FILTER']);

            $this->status = 'ok';
        }catch (\Exception $e){
            $this->status = 'error';
            $this->errors = $e->getMessage();
        }

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