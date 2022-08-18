<?php
namespace Vigr\CustomField\UserType;

use Vigr\CustomField\Integration\Iblock\propertyHeadAndSuccessors;
use Vigr\CustomField\UserField\Types\headAndSuccessors;

class userTypeHeadAndSuccessors
{
    function getUserTypeDescription(): array
    {
        return headAndSuccessors::getUserTypeDescription();
    }

    function getIblockPropertyDescription(): array
    {
        return propertyHeadAndSuccessors::getIBlockPropertyDescription();
    }
}