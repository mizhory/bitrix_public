<?php

namespace Vigr\Budget\Internals;

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
            new Entity\IntegerField('id', [
                'primary' => true,
                'autocomplete' => true,
            ]),


            new Entity\IntegerField('biznesUnit', [
                'required' => false
            ]),

            new Entity\IntegerField('article', [
                'required' => false,
            ]),

            new Entity\IntegerField('year', [
                'required' => false,
            ]),

            new Entity\IntegerField('month', [
                'required' => false,
            ]),

            new Entity\FloatField('plan', [
                'required' => false,
            ]),

            new Entity\FloatField('fact', [
                'required' => false,
            ]),

            new Entity\FloatField('inReserve', [
                'required' => false,
            ]),

            new Entity\FloatField('saldo', [
                'required' => false,
            ]),

            new Entity\FloatField('cumulativeTotal', [
                'required' => false,
            ]),

            new Entity\FloatField('total', [
                'required' => false,
            ]),

            new Entity\StringField('unicHash', [
                'required' => false,
            ]),

            new Entity\StringField('BEName', [
                'required' => false,
            ]),

            new Entity\StringField('ArticleName', [
                'required' => false,
            ])
        ];
    }

}