<?php

namespace Immo\Statements\Grid;

use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Immo\Statements\Data\Department;
use Immo\Statements\Data\Iblock;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\IblockTrait;
use Immo\Statements\Traits\ModuleTrait;

class Filter implements ModuleInterface, GridInterface
{
    use ModuleTrait,
        IblockTrait;

    private $iblockId, $viewMode;

    public function __construct($iblockId, $viewMode)
    {
        $this->iblockId = $iblockId;
        $this->viewMode = $viewMode;
    }

    /**
     * Возвращает массив с параметрами полей фильтра
     *
     * @return array
     */
    public function getFilter()
    {
        $fields = Fields::getFieldsByViewMode($this->viewMode);


        return $this->prepareFields($fields);
    }

    /**
     * Формирует поля для фильтра
     *
     * @param $fields
     * @return array
     */
    public function prepareFields($fields): array
    {
        $results = [];

        foreach ($fields as $field) {
            $data = [
                'id' => $field,
                'type' => Fields::FILTER_BASE_FIELDS_TYPES[$field],
                'name' => self::getPropertyName($field, $this->iblockId),
                'default' => !($field === 'ID'),
                'params' => ['multiple' => 'Y'],
            ];

            if(Fields::FILTER_BASE_FIELDS_TYPES[$field] === Fields::FIELD_TYPE_LIST) {

                switch ($field) {
                    case 'SELECTED_BE':
                    case 'SELECTED_UR':
                        $items = $this->viewMode === 'BE' ? Department::getBusinessUnits() : Department::getCompanies();
                        break;
                    case 'F_YEAR':
                        $items = Iblock::getPropertyFinancialYearsList($this->iblockId);
                        break;
                    default:
                        $items = self::getPropertiesListValues($field, $this->iblockId);
                        break;
                }
                $data['items'] = $items;
            }

            $results[] = $data;
        }

        return $results;
    }

    /**
     * Формирует массив для выборки данных из фильтра
     *
     * @param FilterOptions $options
     * @return array
     */
    public function parseFilter(FilterOptions $options): array
    {
        $filter = [];
        $filterGrid = $options->getFilter();

        $fields = Fields::getFieldsByViewMode($this->viewMode);

        foreach ($fields as &$field) {


            $valueKeyFrom = sprintf('%s_from', $field);
            $valueKeyTo = sprintf('%s_to', $field);
            $fieldMore = sprintf('>%s', $field);
            $fieldLess = sprintf('<%s', $field);

            if(!empty($filterGrid)) {

                switch (Fields::FILTER_BASE_FIELDS_TYPES[$field]) {
                    case Fields::FIELD_TYPE_NUMBER:

                        $tmpKeyNumsel = sprintf('%s_numsel', $field);

                        switch ($filterGrid[$tmpKeyNumsel]) {
                            case 'exact':
                                $filter[$field] = $filterGrid[$valueKeyFrom];
                                break;
                            case 'range':
                                $filter[$field] = [
                                    $filterGrid[$valueKeyFrom],
                                    $filterGrid[$valueKeyTo]
                                ];
                                break;
                            case 'more':
                                $filter[$fieldMore] = $filterGrid[$valueKeyFrom];
                                break;
                            case 'less':
                                $filter[$fieldLess] = $filterGrid[$valueKeyTo];
                                break;
                        }

                        break;
                    case Fields::FIELD_TYPE_DATE:

                        if(!empty($filterGrid[$valueKeyFrom])) {
                            $filter[$field][] = $filterGrid[$valueKeyFrom];
                        }

                        if (!empty($filterGrid[$valueKeyTo])) {
                            $filter[$field][] = $filterGrid[$valueKeyTo];
                        }
                        break;
                    default:



                        if(!empty($filterGrid[$field])) {
                            if(preg_match('/SELECTED_*/', $field, $matches)) {
                                $field = sprintf('%s.VALUE', $field);
                            }

                            $filter[$field] = $filterGrid[$field];
                        }

                        break;
                }
            }
        }

        return $filter;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getColumns(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getRows(): array
    {
        return [];
    }
}