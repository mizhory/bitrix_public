<?php
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$APPLICATION->IncludeComponent(
    'immo:webservice.soap',
    '',
    []
);