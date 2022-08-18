<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\UserTable;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();


class LogsStateList extends CBitrixComponent
{
    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getLogElements(): array
    {
        $arFilter = [
            '=UF_ELEMENT_ID' => (int)$this->arParams['ID'],
        ];

        // Выбор записей в логе
        $sClassActivityLoggingTable = HighloadBlockTable::compileEntity('ActivityLogging')->getDataClass();
        $arSelect = [
            'STAGE_TITLE' => 'UF_STAGE_TITLE',
            'ACTION_TITLE' => 'UF_ACTION_TITLE',
            'COMMENT' => 'UF_COMMENT',
            'DATE_CREATE' => 'UF_DATE_CREATE',
            'USER_ID' => 'USER.ID',
            'USER_NAME' => 'USER.NAME',
            'USER_SECOND_NAME' => 'USER.SECOND_NAME',
            'USER_LAST_NAME' => 'USER.LAST_NAME',
        ];
        $resActivityLogging = $sClassActivityLoggingTable::getList([
            'filter' => $arFilter,
            'select' => array_merge($arSelect, ['ID']),
            'runtime' => [
                new ReferenceField(
                    'USER',
                    UserTable::class,
                    ['this.UF_USER_ID' => 'ref.ID'],
                    ['join_type' => Join::TYPE_LEFT]
                )
            ]
        ]);
        $arResult = [];
        while ($arActivityLogging = $resActivityLogging->fetch()) {
            $arResult[] = $arActivityLogging;
        }
        return $arResult;
    }

    /**
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function executeComponent()
    {
        $this->arResult['ELEMS'] = $this->getLogElements();
        $this->includeComponentTemplate();
    }
}