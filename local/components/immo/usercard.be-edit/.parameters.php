<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
IncludeTemplateLangFile(__FILE__);

$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "STRICT_SECTION_CHECK" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("CP_BNL_STRICT_SECTION_CHECK"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "CACHE_TIME" => array("DEFAULT" => 36000000),
        "CACHE_FILTER" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("IBLOCK_CACHE_FILTER"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "CACHE_GROUPS" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("CP_BNL_CACHE_GROUPS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
    ),
);