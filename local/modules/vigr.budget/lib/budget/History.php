<?php
namespace Vigr\Budget;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Type\DateTime;
use Immo\Tools\User;
use Vigr\Budget\Internals\HistoryTable;

/**
 * Class History
 * @package Vigr\Budget
 */
class History
{
    /**
     * Первичная загрузка бюджет
     */
    const START_BUDGET = 1;
    /**
     *Обновление бюджета
     */
    const UPDATE_BUDGET = 2;
    /**
     *Резервирование средств
     */
    const RESERVE = 3;
    /**
     *Перерезервирование средств
     */
    const RERESERVE = 4;
    /**
     *Списание средств
     */
    const FACT = 5;
    /**
     *Отмена резервирования средств
     */
    const CANCEL = 6;
    /**
     *Перераспределение средств бюджета
     */
    const RE_BUDGET = 7;

    /**
     *
     */
    const TYPES = [
        'BUDGET' => 'Загрузка бюджета',
        'RE_BUDGET' => 'Работа со средствами бюджета',
        'BUDGET_ERROR' => 'Ошибка при загрузке бюджета'
    ];

    /**
     * @param $hash
     * @param $message
     * @param $action
     * Добавляет сообщение в таблицу
     * @param string $dealType
     * @param int $be
     * @param int $article
     * @param int $cardId
     * @param int $iblockId
     * @throws \Exception
     */
    public function addMessage($hash,$message,$action,$dealType = self::TYPES['BEZNAL'], $be = 0, $article = 0, $cardId = 0, $iblockId = 0){
        global $USER;

        $date = new DateTime();

        HistoryTable::add(
            [
                'unicHash'=>$hash,
                'action'=>$action,
                'user'=>$USER->GetID(),
                'date'=>$date,
                'message'=>$message,
                'DEAL_TYPE'=>$dealType,
                'id_be' => $be,
                'id_article' => $article,
                'id_card' => $cardId,
                'iblock_id_card' => $iblockId,
            ]
        );
    }
}