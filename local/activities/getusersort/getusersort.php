<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetUserSort
    extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'code' => '',
            'IDDDD'=>'',
            'arElements'=>''
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

        $arEl = CIBlockElement::getList(
            [],
            [
                'ID'=>$this->IDDDD
            ],
            false,
            false,
            [
                'PROPERTY_'.$this->code
            ]
        )->fetch();

        $arJson = json_decode($arEl['PROPERTY_'.$this->code.'_VALUE'],1);

        $arUsersIds = [];

        foreach ($arJson['usersIdsByPlace'] as $key=>$place){
            $arUsersIds[] = $key;
        }

        $dbElsOts = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID'=>26,
                'PROPERTY_SOTRUDNIK'=>$arUsersIds
            ],
            false,
            false,
            [
                'PROPERTY_IO',
                'PROPERTY_SOTRUDNIK'
            ]
        );

        $arForReplace = [];

        while($arElsOst = $dbElsOts->fetch()){
            $arForReplace[$arElsOst['PROPERTY_SOTRUDNIK_VALUE']] = $arElsOst['PROPERTY_IO_VALUE'];
        }

        $dbElsOts = CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID'=>27,
                'PROPERTY_FIO_UVOLNYAEMOGO'=>$arUsersIds
            ],
            false,
            false,
            [
                'PROPERTY_PREEMNIK',
                'PROPERTY_FIO_UVOLNYAEMOGO'
            ]
        );


        while($arElsOst = $dbElsOts->fetch()){
            $arForReplace[$arElsOst['PROPERTY_FIO_UVOLNYAEMOGO_VALUE']] = $arElsOst['PROPERTY_PREEMNIK_VALUE'];
        }

        $arNewArray = [];

        foreach ($arJson['usersIdsByPlace'] as $userKey=>$userPlace){
            if(array_key_exists($userKey,$arForReplace)){
                $arNewArray[$userPlace] = $arForReplace[$userKey];
            }else{
                $arNewArray[$userPlace] = $userKey;
            }

        }

        ksort($arNewArray);
        $arForStr = [];
        foreach ($arNewArray as $user){
            $arForStr[] = 'user_'.$user;
        }

        $this->arElements = implode(',',$arForStr);

        return CBPActivityExecutionStatus::Closed;
    }

    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
    {

		/*
        $arCurrentValues['Responsible'] = CBPHelper::UsersArrayToString(
            $arCurrentValues['Responsible'], $arWorkflowTemplate, $documentType);

        if($arCurrentValues['propId'] > 0 && $listId > 0){
            $dbProps = CIBlockProperty::GetList(
                [],
                [
                    'IBLOCK_ID'=>$listId
                ]
            );

            $arProps = [];

            while($arProp = $dbProps->fetch()){
                $selected = '';
                if($arProp['ID'] === $arCurrentValues['propId']){
                    $selected = 'selected';
                }
                $arProp['selected'] = $selected;
                $arProps[] = $arProp;
            }
        }
		*/
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
            'code' => $arCurrentValues['code'],
            'IDDDD' => $arCurrentValues['IDDDD']
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