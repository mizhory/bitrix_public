<?php
use Bitrix\Main\UI\Extension;

/**
 * Class CBudgetTable
 * Класс таблицы
 */
class CBudgetTable extends CBitrixComponent
{
    public function executeComponent()
    {
        Extension::load('ui.bootstrap4');

        $this->arResult['filterFields'] = $this->arParams['filterFields'];
        $this->arResult['data'] = $this->arParams['data'];
        $this->arResult['filterId'] = $this->arParams['filterId'];

        $this->IncludeComponentTemplate();
    }
}