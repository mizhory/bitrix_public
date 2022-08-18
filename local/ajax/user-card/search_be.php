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
$save = $request->getQuery('save');

$src = $request->getPost('src');
$notHtml = $request->getPost('notHtml');

if (!$exec || $exec != 'true') {
    exit(getMessage('ERROR_NOT_EXEC'));
}


if ($save == 'true') {
    $USER_ID = $request->getQuery('uid');
    $salaryBESelect = $request->getQuery('salaryBeSelect');
    $salaryUpdate = UserCardManager::updateUserSalary($USER_ID, $salaryBESelect);
    if ($salaryUpdate == true) {
        exit(getMessage('SUCCESS_MSG'));
    } else {
        exit($salaryUpdate);
    }
} else {
    $USER_ID = $request->getPost('uid');
    if (!intval($USER_ID)) {
        $USER_ID = $request->getQuery('uid');
    }
    list($BEList, $userReturn) = UserCardManager::getListNSearchByName($USER_ID, [], $src);

    $UF_CS_BE = json_decode($userReturn['UF_CS_BE'], 1);

    foreach ($BEList as $d) {
        $selected = '';
        if ($d['ID'] == $UF_CS_BE['salaryBeSelect']) {

            $selected = ' selected="selected"';
            $selectedName = $d['NAME'];
        }
        if ($notHtml != 'true') {
            print "<option{$selected} value='" . $d['ID'] . "'" . $selected . ">" . $d['NAME'] . "</option>";
        }
    }

    if ($notHtml == 'true') {
        print $selectedName;
    }
    exit();
}