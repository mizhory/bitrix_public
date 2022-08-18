<?php
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$sing = $_REQUEST['sign'];
$signer = new \Bitrix\Main\Security\Sign\Signer;
$uns = $signer->unsign($sing, md5('18.10.2021'));

if($uns !== 'files'){
    die('not');
}
CModule::includeModule('vigr.budget');

$downloader = new \Vigr\Budget\FileDownloaderExcel();

$downloader->checkFiles();

