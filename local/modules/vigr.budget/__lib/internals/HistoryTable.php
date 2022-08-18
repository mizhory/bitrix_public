<?php
namespace Vigr\Budget\Internals;

use Bitrix\Main\Entity;

/**
 * Class HistoryTable
 * @package Vigr\Budget\Internals
 * Таблица истории бюджета
 */
class HistoryTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'vigr_budget_history';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('id', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new Entity\StringField('unicHash', [
                'required' => false,
            ]),

            new Entity\StringField('action', [
                'required' => false,
            ]),

            new Entity\StringField('user', [
                'required' => false,
            ]),

            new Entity\TextField('message', [
                'required' => false,
            ]),

            new Entity\DateTimeField('date', [
                'required' => false,
            ])
        ];
    }

}