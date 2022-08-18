<?
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;
$class = \CBitrixComponent::includeComponentClass('immo:budget.list');
/** @var \Immo\Components\BudgetList $object */
$object = new $class();
$object->arParams = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

$object->excelAction();