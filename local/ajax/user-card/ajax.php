<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeTemplateLangFile(__FILE__);

global $APPLICATION, $USER;

use \Bitrix\Main\{
    UserTable,
    Application
};
use \IMMO\Manager\UserCardManager;

$request = Application::getInstance()->getContext()->getRequest();

$exec = $request->getQuery('exec');

if (!$exec || $exec != 'true') {
    exit(getMessage('ERROR_NOT_EXEC'));
}

$arFlds = [];
$USER_ID = false;

list($USER_ID, $UF_USER_TYPE, $arFields) = UserCardManager::getAjaxDataForUpdateUserInContext();

if (!intval($USER_ID))
    exit(getMessage('USER_NOT_DEFINED'));


$updateComplete = UserCardManager::updateComplete($arFields, $USER_ID, $other_fields = [
    'UF_USER_TYPE' => $UF_USER_TYPE
]);

if ($updateComplete == true)
    exit(getMessage('SUCCESS_MSG'));

elseif ($updateComplete->LAST_ERROR)
    exit($updateComplete->LAST_ERROR);

else
    exit(getMessage('UNDEFINED_ERROR'));