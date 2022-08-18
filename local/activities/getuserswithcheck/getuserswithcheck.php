<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetUsersWithCheck
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'user' => '',
            'userStr' => '',
            'error' => '',
            'chislo'=>'',
            'depId'=>'',
            'status' => '',
        ];

        $this->SetPropertiesTypes([
            'userStr' => ['Type' => 'string']
        ]);
    }

    public function Execute()
    {
        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();

        if(!is_array($this->user)){
            $arUsersExp = explode(',',$this->user);

            foreach ($arUsersExp as $key=>$value){
                $arUsersExp[$key] = trim(preg_replace('/;/','',$value));
            }
        }else{
            $arUsersExp = $this->user;
        }


        $arUsers = CBPHelper::ExtractUsers($arUsersExp, $documentId);




        $arNew = [];

        foreach ($arUsers as $user){
            $result = \Vigr\Helpers\Helper::checkUserByCheckList($user,$this->depId);

            if($result->status == 'error'){
                $this->status = 'error';
                $this->error = $result->error;
                return CBPActivityExecutionStatus::Closed;
            }else{
                if($this->chislo == 'N'){
                    $arNew[] = 'user_'.$result['userId'];
                }else{
                    $arNew[] = $result['userId'];
                }

            }
        }

        $this->userStr=implode(',',$arNew);

        return CBPActivityExecutionStatus::Closed;
    }

    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
    {
        $dbIblocks = CIBlock::GetList(
            [],
            [
                'TYPE'=>'lists'
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

        $listId = 0;

        if($arCurrentValues['listId'] > 0){
            $listId = $arCurrentValues['listId'];
        }

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
            'user' => $arCurrentValues['user'],
            'depId' => $arCurrentValues['depId'],
            'chislo' => $arCurrentValues['chislo']
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