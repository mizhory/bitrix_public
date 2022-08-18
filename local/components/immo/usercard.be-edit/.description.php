<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
IncludeTemplateLangFile(__FILE__);
$arComponentDescription = array(
    "NAME" => GetMessage("T_IBLOCK_DESC_LIST"),
    "DESCRIPTION" => GetMessage("T_IBLOCK_DESC_LIST_DESC"),
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "immo-cmpn",
        "CHILD" => array(
            "ID" => "immo-cmpn.usercard.be-edit",
            "NAME" => GetMessage("T_IBLOCK_DESC_NEWS"),
            "SORT" => 100,
        ),
    ),
);