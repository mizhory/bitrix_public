<?php
namespace Vigr\CustomField\UserType;

use Vigr\CustomField\Integration\Iblock\propertyVacationDays;
use Vigr\CustomField\UserField\Types\vacationDaysType;

class userTypeVacationDays
{
    function getUserTypeDescription(): array
    {
        return vacationDaysType::getUserTypeDescription();
    }

    function getIblockPropertyDescription(): array
    {
        return propertyVacationDays::getIBlockPropertyDescription();
    }
}