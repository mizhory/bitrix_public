<?php
namespace Vigr\CustomField\UserType;

use Vigr\CustomField\Integration\Iblock\propertyUlAndHeads;
use Vigr\CustomField\UserField\Types\ulAndHeadsType;

class userTypeUlAndHeads
{
    function getUserTypeDescription(): array
    {
        return ulAndHeadsType::getUserTypeDescription();
    }

    function getIblockPropertyDescription(): array
    {
        return propertyUlAndHeads::getIBlockPropertyDescription();
    }
}