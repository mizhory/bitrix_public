<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetDealOstActivity
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'dealId'=>'',
            'ost'=>'',
        ];

        $this->SetPropertiesTypes([
            'ost' => ['Type' => 'string']
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

        $arProducts = $deal->getDealProducts($this->dealId,false);

        $sum = 0;

        foreach ($arProducts as $product){
            $sum += $product['summa'];
            $sum -= $product['payInValute'];
        }

        $ost = round($sum,2);

        $this->ost = $ost;

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