<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


class CBPbitroidTerminateWorkflow extends CBPActivity {

	
	public function Execute() {
		if (!CModule::IncludeModule('bizproc')){
			return $this->sendErrorAnswer('Module "bizproc" required!');
		}
		$workflowID = $this->WORKFLOW_ID;
		$status = $this->STATUS;
		if(!is_string($workflowID) || empty($workflowID)){
			return $this->sendErrorAnswer("Неверный ID бизнес-процесса $workflowID");
		}
		
		if(CBPDocument::TerminateWorkflow($workflowID, false, $this->executionErrors, $status)){
			return $this->sendSuccessAnswer();
		}
		return $this->sendErrorAnswer();
	}
	
	
	
	
	
	
	public $executionErrors = [];
	public function sendErrorAnswer ($erMess = []) {
		global $APPLICATION;
		$this->SetStatus(CBPActivityExecutionStatus::Faulting);
		$this->executionResult = CBPActivityExecutionResult::Faulted;
		if(is_string($erMess)) $erMess = [$erMess];
		$arErrors = array_merge($erMess, $this->executionErrors);
		$apliError = $APPLICATION->GetException();
		if(!empty($apliError)){
			$arErrors[] = strval($apliError);
		}
		if (is_array($arErrors)) {
			$strErrors = implode('  |||  ', $arErrors);
			foreach ($arErrors as $erM) {
				if (is_string($erM) && !empty($erM)) {
					$this->WriteToTrackingService($erM, $GLOBALS['USER']->getId(), 4); // ошибка действия
				}
			}
			$this->TERMINATE_SUCCESS = 'N';
			$this->ERROR_STR = $strErrors;
		}
		return CBPActivityExecutionStatus::Closed;
	}
	
	
	
	
	
	public function sendSuccessAnswer() {
		$this->executionResult = CBPActivityExecutionResult::Succeeded;
		$this->SetStatus(CBPActivityExecutionStatus::Closed);
		$msg = "Бизнес-процесс успешно остановлен ({$this->WORKFLOW_ID})";
		$this->WriteToTrackingService($msg, $GLOBALS['USER']->getId(), CBPTrackingType::Report);
		$this->TERMINATE_SUCCESS = 'Y';
		$this->ERROR_STR = null;
		return CBPActivityExecutionStatus::Closed;
	}
	
	
	
	
	
	

	/*  стандартные методы, в большинстве случаев не требуется их менять  */
	public static $thisActivityDescr = [];
	public static function getThisActivityDescription() {
		if (empty(self::$thisActivityDescr)) {
			$code = basename(__DIR__);
			$rootActivity = CBPRuntime::GetRuntime();
			$descr = $rootActivity->GetActivityDescription($code);
			$descr['CODE'] = $code;
			self::$thisActivityDescr = $descr;
		}
		return self::$thisActivityDescr;
	}

	
	
	

	public function __construct($name) {
		parent::__construct($name);
		$descr = self::getThisActivityDescription();
		$this->arProperties['Title'] = $descr['NAME'];
		if (!empty($descr['INPUT_PROPERTIES']) && is_array($descr['INPUT_PROPERTIES'])) {
			foreach ($descr['INPUT_PROPERTIES'] as $propID => $propInfo) {
				$this->arProperties[$propID] = '';
			}
		}
		if (!empty($descr['RETURN']) && is_array($descr['RETURN'])) {
			foreach ($descr['RETURN'] as $propID => $propInfo) {
				$this->arProperties[$propID] = '';
			}
		}
	}
	
	
	
	

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "") {
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName
		]);
		$descr = self::getThisActivityDescription();
		$map = [];
		foreach ($descr['INPUT_PROPERTIES'] as $fID => $fInfo) {
			$map[$fID] = [];
			$map[$fID]['FieldName'] = $fID;
			foreach ($fInfo as $key => $value) {
				$map[$fID][ucfirst(strtolower($key))] = $value;
			}
		}
//		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
//		$dialog->setRuntimeData([
//			'BIZPROC_PARAMS' => $currentActivity['Properties']['BIZPROC_PARAMS']
//		]);
		$dialog->setMap($map);

		return $dialog;
	}
	
	
	
	

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors) {
		$descr = self::getThisActivityDescription();
		$props = [];
		foreach ($descr['INPUT_PROPERTIES'] as $fID => $fInfo) {
			$textValue = $arCurrentValues[$fID . '_text'];
			if (
					$fInfo['REQUIRED'] && $fInfo['REQUIRED'] !== 'N' && empty($arCurrentValues[$fID]) && empty($textValue)
			) {
				$arErrors[] = ["code" => "emptyCode", "message" => 'Укажите "' . $fInfo['NAME'] . '"'];
			}
			
			$props[$fID] = $arCurrentValues[$fID];
			if (empty($arCurrentValues[$fID]) && !empty($textValue)) {
				$props[$fID] = $textValue;
			}
		}
		if (count($arErrors) > 0)
			return false;

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $props;
		return true;
	}
	
}