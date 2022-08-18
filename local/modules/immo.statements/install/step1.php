<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

check_bitrix_sessid() || die();

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$request = Context::getCurrent()->getRequest();

global $APPLICATION;

if($e = $APPLICATION->GetException()):
    CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage("IMMO_STATEMENTS_INSTALL_ERROR"),
        'DETAILS' => $e->GetString(),
        'HTML' => true
    ]);
else:
    CAdminMessage::ShowNote(Loc::getMessage("IMMO_STATEMENTS_INSTALL_SUCCESS")); ?>

    <form action="<?=$request->getRequestedPage()?>">
        <input type="hidden" name="lang" value="<?=LANG?>">
        <input type="submit" value="<?= Loc::getMessage("IMMO_STATEMENTS_INSTALL_BACK") ?>">
    </form>

<?php endif;