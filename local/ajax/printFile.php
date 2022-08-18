<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

(new \Immo\Tools\File\FileDownload(\Bitrix\Main\Context::getCurrent()->getRequest()))->download();
