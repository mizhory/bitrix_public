<?php

namespace Immo\Statements\Grid;

interface GridInterface
{
    public function prepareFields($fields): array;

    public function getColumns(): array;

    public function getRows(): array;
}