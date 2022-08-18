<?php

namespace Immo\Statements\Services;

use Immo\Statements\Data\HLBlock;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\ModuleTrait;

class StatementsService implements ModuleInterface
{
    use ModuleTrait;

    public function getHlBlock($entityName): HLBlock
    {
        return HLBlock::createInstance($entityName);
    }

    public function generateStatement()
    {

    }

    public function prepareEmployees(): array
    {
        $employees = [];
        $elements = $this->getHlBlock(self::HL_ENTITY_COMPANY_EMPLOYEES_DATA);

        foreach ($elements as $element) {

        }

        return $employees;
    }
}