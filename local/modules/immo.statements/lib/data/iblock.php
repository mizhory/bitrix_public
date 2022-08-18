<?php

namespace Immo\Statements\Data;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\IblockTrait;
use Immo\Statements\Traits\ModuleTrait;

class Iblock implements ModuleInterface
{
    use IblockTrait,
        ModuleTrait;

    public static function getPropertyFinancialYearsList($iblockId, $elementId = null)
    {
        $values = [];
        $params = [
            'select' => [
                'ID',
                'NAME',
                'IBLOCK_ID',
                'P.IBLOCK_ID',
                'P.LINK_IBLOCK_ID'
            ],
            'filter' => [
                'P.IBLOCK_ID' => $iblockId,
                'P.CODE' => 'F_YEAR'
            ],
            'runtime' => [
                new Reference('P', PropertyTable::class, Join::on('this.IBLOCK_ID', 'ref.LINK_IBLOCK_ID'))
            ]
        ];

        if(!is_null($elementId)) {
            $params['filter']['ID'] = $elementId;
        }

        $list = ElementTable::getList($params);

        foreach ($list->fetchAll() as $item) {
            $values[$item['ID']] = $item['NAME'];
        }

        return $values;
    }


}