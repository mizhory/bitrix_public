<?php

namespace Vigr\Budget\Internals;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Entity;

/**
 * Class BudgetTable
 * @package Vigr\Budget\Internals
 * Таблица бюджета
 */
class BudgetTable extends Entity\DataManager
{

    public static function getTableName()
    {
        return 'vigr_budget_be';
    }

    public static function getMap()
    {
        return [
            'id' => new Entity\IntegerField('id', [
                'primary' => true,
                'autocomplete' => true,
            ]),


            'biznesUnit' => new Entity\IntegerField('biznesUnit', [
                'required' => false
            ]),

            'article' => new Entity\IntegerField('article', [
                'required' => false,
            ]),

            'year' => new Entity\IntegerField('year', [
                'required' => false,
            ]),

            'month' => new Entity\IntegerField('month', [
                'required' => false,
            ]),

            'plan' => new Entity\FloatField('plan', [
                'required' => false,
            ]),

            'fact' => new Entity\FloatField('fact', [
                'required' => false,
            ]),

            'inReserve' => new Entity\FloatField('inReserve', [
                'required' => false,
            ]),

            'saldo' => new Entity\FloatField('saldo', [
                'required' => false,
            ]),

            'cumulativeTotal' => new Entity\FloatField('cumulativeTotal', [
                'required' => false,
            ]),

            'total' => new Entity\FloatField('total', [
                'required' => false,
            ]),

            'unicHash' => new Entity\StringField('unicHash', [
                'required' => false,
            ]),

            'BEName' => new Entity\StringField('BEName', [
                'required' => false,
            ]),

            'ArticleName' => new Entity\StringField('ArticleName', [
                'required' => false,
            ])
        ];
    }

}