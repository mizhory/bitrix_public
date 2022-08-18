<?php

namespace Immo\Statements\Component;

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ORM\Query\Join;
use CBitrixComponent;
use Immo\Statements\Access\User;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\IblockTrait;
use Immo\Statements\Traits\ModuleTrait;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

abstract class StatementsComponent extends CBitrixComponent implements ModuleInterface, Controllerable
{
    use ModuleTrait,
        IblockTrait;

    public const SALARY_STATEMENTS_GRID_ID = 'statements_list';

    protected User $user;

    /**
     * @param CBitrixComponent $component
     * @throws ObjectNotFoundException
     */

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->setUser(self::getContainer()->get('immo:access.user'));
    }

    public function setUser(User $user): StatementsComponent
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function configureActions()
    {
        // TODO: Implement configureActions() method.
    }

    /**
     * Формирует массив $arResult
     */
    abstract public function prepareResult();

    final public function executeComponent()
    {

        $this->prepareResult();
        $this->includeComponentTemplate($this->preparePage());
    }

    /**
     * Формирует название страницы в шаблоне
     *
     * @return string
     */
    protected function preparePage(): string
    {
        return $this->arResult['PAGE'] ?? $this->arResult['page'] ?? '';
    }
}