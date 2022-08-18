<?php
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');


$GLOBALS['APPLICATION']->RestartBuffer();

use Bitrix\Main\Application;


$request = Application::getInstance()->getContext()->getRequest();
$action = $request->getPost("action");

$bizUnitId = $request->getPost("BIZ_UNIT_ID");
$bizUnitKey = $request->getPost("BIZ_UNIT_KEY");
$bizUnitSum = $request->getPost("BIZ_UNIT_SUM");

global $USER;

ob_start();
$APPLICATION->IncludeComponent(
    "de:bizunit.products.widgetform.edit",
    "",
    [
        'AJAX_MODE' => 'Y',
        'BIZ_UNIT_KEY' => $bizUnitKey,
        'BIZ_UNIT_ID' => $bizUnitId,
        'ALL_SUM' => $bizUnitSum,
        'ALL_PSNT' => 100,

       'AJAX'=>'Y',

       'DATA_BIZ_UNIT_PRODUCTS' => [
            [
                'BIZ_UNIT_PRODUCT_ID' => -1,
                'PSNT' => 100,
                'SUM' => $bizUnitSum,
            ],
        ],

        'PREFIX_ID' => 'biz_productunit'.$bizUnitId,
        'ENTITY_ID' => $entityId,
        'ENTITY_TYPE' => 'RPA_2',
    ],
    null
);
$itemProduct = ob_get_contents();
ob_end_clean();
echo $itemProduct;
