<?php
namespace Vigr\UserCard;
class ksLegalEntitiesUserType{
    const USER_TYPE_ID = 'vigrusercardlegalentities',
        RENDER_COMPONENT = 'vigr:user.card';
    function getUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => static::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => "КС-Юрлица",
            "BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
            "VIEW_CALLBACK" => array(__CLASS__,'getPublicView'),
            "EDIT_CALLBACK" => array(__CLASS__,'getPublicView'),
        );
    }
    function getEditFormHtml($arProperty, $value){
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'vigr:user.card',
            'ksLegal.admin.view',
            [
                'userField' => $arProperty,
            ]
        );
        $s = ob_get_contents();
        ob_end_clean();
        return  $s;
    }
    function getPublicView($arProperty, $value){
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'vigr:user.card',
            'ksLegal.public.view',
            [
                'userField' => $arProperty,
            ]
        );
        $s = ob_get_contents();
        ob_end_clean();
        return $s;
    }
    function getDbColumnType(){
        return 'text';
    }
}