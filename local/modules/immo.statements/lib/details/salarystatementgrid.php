<?php

namespace Immo\Statements\Details;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Immo\Statements\Data\HLBlock;
use Immo\Statements\Grid\AbstractGrid;
use Immo\Statements\ModuleInterface;

class SalaryStatementGrid extends AbstractGrid implements ModuleInterface
{

    private int $elementId;
    private string $role;
    private HLBlock $hlBlock;
    private Grid $grid;

    /**
     * @param Grid $grid
     * @return SalaryStatementGrid
     */
    public function setGrid(Grid $grid): SalaryStatementGrid
    {
        $this->grid = $grid;
        return $this;
    }

    /**
     * @param int $elementId
     * @param string $role
     * @param HLBlock $hlBlock
     */
    public function __construct(HLBlock $hlBlock, int $elementId, string $role)
    {
        $this->elementId = $elementId;
        $this->role = $role;
        $this->hlBlock = $hlBlock;

        $this->setGrid(
            new Grid($role, $hlBlock)
        );
    }

    public function prepareFields($fields): array
    {
        return $this->grid->prepareFields($fields);
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getColumns(): array
    {
        $fields = Fields::getFieldsByRole($this->role, $this->hlBlock);
        return $this->prepareFields($fields);
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getRows($filter = [], $order = []): array
    {
        return $this->grid->getRows($filter, $order);
    }
}