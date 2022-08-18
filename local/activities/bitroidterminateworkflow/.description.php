<?php

$arActivityDescription = [
	"NAME" => 'Остановить БП в коробке',
	"DESCRIPTION" => "Действие позволяет остановить бизнес-процесс в коробке",
	"TYPE" => array(
		'activity', 
	),
	"CLASS" => "bitroidTerminateWorkflow",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"OWN_ID" => "bitroidActivities",
		"OWN_NAME" => 'Битройд',
	),
	"INPUT_PROPERTIES"=> [
		'WORKFLOW_ID' => [
			'NAME' => 'ID бизнес-процесса',
			'TYPE' => 'string',
			'REQUIRED' => true,
		],
		'STATUS' => [
			'NAME' => 'Текст статуса',
			'TYPE' => 'string',
		],
	],
	'RETURN' => [
		'TERMINATE_SUCCESS' => [
			'NAME' => 'БП успешно остановлен?',
			'TYPE' => 'bool',
			'DEFAULT' => 'N',
		],
		'ERROR_STR' => [
			'NAME' => 'Текст ошибки',
			'TYPE' => 'string',
		],
	],
];

return $arActivityDescription;