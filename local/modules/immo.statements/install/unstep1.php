<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

check_bitrix_sessid() || die();

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$request = Context::getCurrent()->getRequest();

?>

<form action="<?=$request->getRequestedPage()?>" method="post">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="immo.statements">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">

    <label class="ui-ctl ui-ctl-checkbox">

        <div class="ui-ctl-label-text"><input type="checkbox" class="ui-ctl-element" name="save_tables" value="Y" checked> <?= Loc::getMessage("IMMO_STATEMENTS_LABEL_SAVE_TABLES") ?></div>
    </label>

    <input name="inst" type="submit" value="<?= Loc::getMessage("IMMO_STATEMENTS_LABEL_DELETE_MODULE") ?>">
</form>
