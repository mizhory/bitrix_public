<?php

namespace Vigr\Budget\Internals;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Entity;

class BudgetTechTable extends Entity\DataManager
{

    public static function getTableName()
    {
        return 'vigr_budget_be';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('id', [
                'primary' => true,
                'autocomplete' => true,
            ]),


            new Entity\StringField('fileName', [
                'required' => false
            ]),

            new Entity\IntegerField('month', [
                'required' => false,
            ]),

            new Entity\IntegerField('year', [
                'required' => false,
            ]),

            new Entity\IntegerField('plan', [
                'required' => false,
            ]),

            new Entity\StringField('unicHash', [
                'required' => false,
            ])
        ];
    }

}