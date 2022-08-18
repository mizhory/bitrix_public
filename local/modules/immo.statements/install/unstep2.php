<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
check_bitrix_sessid() || die();

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::loadMessages(__DIR__ . '/step1.php');

$request = Context::getCurrent()->getRequest();

CAdminMessage::ShowNote(Loc::getMessage("INFO_STATEMENTS_UNINSTALL_MODULE_SUCCESS"));
?>

<form action="<?=$request->getRequestedPage()?>">
    <input type="hidden" name="lang" value="<?=LANG?>">
    <input type="submit" value="<?= Loc::getMessage("IMMO_STATEMENTS_INSTALL_BACK") ?>">
</form>
