<?php


namespace Vigr\Budget\UserType;


use Vigr\Budget\Integration\Iblock\SortFields;
use Vigr\Budget\UserField\Types\PropertySortFieldsType;

class dadatafield
{
    public const
        RENDER_COMPONENT = 'vigr:budget.dadata';
    function getUserTypeDescription(){
        return PropertySortFieldsType::getUserTypeDescription();
    }

    function getIBlockPropertyDescription(){
        return SortFields::getIBlockPropertyDescription();
    }
}