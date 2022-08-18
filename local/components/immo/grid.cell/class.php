<?php

namespace Immo\Statements;

use Immo\Statements\Component\StatementsComponent;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class GridCellComponent extends StatementsComponent
{

    /**
     * @return mixed
     */
    public function prepareResult()
    {
        foreach ($this->arParams as $key => $value) {
            if(!preg_match('/\~/', $key) && $key !== 'CACHE_TYPE') {
                $this->arResult[$key] = $value;
            }
        }

        return $this->arResult;
    }
}