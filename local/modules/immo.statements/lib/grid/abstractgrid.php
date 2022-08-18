<?php

namespace Immo\Statements\Grid;

abstract class AbstractGrid implements GridInterface
{

    abstract public function prepareFields($fields): array;

    /**
     * Возвращает массив с отображаемым количеством строк в гриде
     *
     * @return array
     */
    public function getPageSizes()
    {
        $pageSizes = [];
        $sizes = [5,10,20,50,100,200,500];

        foreach ($sizes as $size) {
            $pageSizes[] = [
                'NAME' => $size,
                'VALUE' => $size
            ];
        }

        return $pageSizes;
    }

    abstract public function getColumns(): array;

    abstract public function getRows(): array;

    /**
     * Подготавливает поля для orm
     *
     * @param $fields
     * @return array
     */
    public function prepareSelectFields($fields): array
    {
        $prepared = [];
        foreach ($fields as $field) {
            $key = sprintf('%s_', $field);
            $prepared[$key] = $field;
        }

        array_unshift($prepared, 'ID', 'NAME');

        return $prepared;
    }
}