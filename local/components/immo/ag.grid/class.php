<?php

namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @description Класс обертка для работы с грид таблицей (ag.grid)
 * Class AgGrid
 * @package Immo\Components
 */
class AgGrid extends \CBitrixComponent
{
    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->IncludeComponentTemplate();
    }
}
