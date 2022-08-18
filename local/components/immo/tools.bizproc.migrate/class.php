<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Class IblockTemplateCard
 * @package Immo\Components
 */
class ToolsBizprocMigrate extends \CBitrixComponent
{
    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}