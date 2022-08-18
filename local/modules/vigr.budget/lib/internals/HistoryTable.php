<?php
namespace Vigr\Budget\Internals;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
            'id' => new Entity\IntegerField('id', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            'unicHash' => new Entity\StringField('unicHash', [
                'required' => false,
            ]),

            'action' => new Entity\StringField('action', [
                'required' => false,
            ]),

            'user' => new Entity\StringField('user', [
                'required' => false,
            ]),

            'message' => new Entity\TextField('message', [
                'required' => false,
            ]),

            'date' => new Entity\DateTimeField('date', [
                'required' => false,
            ]),

            'DEAL_TYPE' => new Entity\StringField('DEAL_TYPE', [
                'required' => false,
            ]),

            'type' => new Entity\StringField('type', []),

            'type_name' => new Entity\StringField('type_name', []),

            'id_card' => new Entity\IntegerField('id_card', []),

            'iblock_id_card' => new Entity\IntegerField('iblock_id_card', []),

            'id_be' => new Entity\IntegerField('id_be', []),

            'id_article' => new Entity\IntegerField('id_article', []),
        ];
    }

}