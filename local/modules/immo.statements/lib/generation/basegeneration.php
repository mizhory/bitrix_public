<?php


namespace Immo\Statements\Generation;


/**
 * @description Интерфейс для работы с генерациями
 * Interface BaseGeneration
 * @package Immo\Statements\Generation
 */
interface BaseGeneration
{
    /**
     * @description Метод для загрузки данных из бд
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function loadRows(array $filter = [], int $limit = 0, int $page = 1): array;

    /**
     * @description Метод для генерации данных
     */
    public function generate(): void;
}