<?php
namespace Vigr\Budget\UserType;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Vigr\Budget\Integration\Iblock\SortFields;
use Vigr\Budget\UserField\Types\PropertySortFieldsType;

class UserTypeSortFields{
    public const
        RENDER_COMPONENT = 'bitrix:main.field.datetime';
    function getUserTypeDescription(){
        return PropertySortFieldsType::getUserTypeDescription();
    }

    function getIBlockPropertyDescription(){
        return SortFields::getIBlockPropertyDescription();
    }
}