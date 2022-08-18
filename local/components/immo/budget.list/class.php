<?php

namespace Immo\Components;

use Bitrix\Main;
use Immo\Integration\Budget\BudgetHelper;
use Immo\Structure\Organization;
use Bitrix\Main\Engine\ActionFilter;
use Immo\Tools\File\ExcelGeneration;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @description Компонент для работы со списком бюджета
 * Class BudgetList
 * @package Immo\Components
 */
class BudgetList extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    use \Immo\Integration\Budget\BudgetList;

    /**
     * @return mixed|void|null
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function executeComponent()
    {
        Main\Loader::includeModule('vigr.budget');
        $this->buildTableParams();
        $this->IncludeComponentTemplate();
    }

    /**
     * @description описание префильтров/фильтров действий
     * @return \array[][]
     */
    public function configureActions()
    {
        return [
            'loadData' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
            'excel' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    /**
     * @description Метод загрузки данных
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function loadDataAction(): array
    {
        $this->buildData();
        return $this->arResult['DATA'] ?? [];
    }

    /**
     * @description Генерирует и возвращает эксель документ
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     * @throws Main\SystemException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function excelAction()
    {
        $this->buildData();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        $mergeColumns = $excelColumns = [];
        foreach ($this->arResult['COLUMNS'] as $index => $column) {
            $letter = ExcelGeneration::getLetter($index);

            $sheet
                ->getCell("{$letter}{$row}")
                ->setValue($column['headerName']);

            $sheet->getColumnDimension($letter)->setWidth($column['excelWidth']);

            $excelColumns[$column['field']] = [
                'letter' => $letter,
            ];

            if (in_array($column['field'], ['year', 'BE_NAME', 'article'])) {
                $mergeColumns[$column['field']] = $letter;
            }
        }

        $arrayColumns = [
            'budget',
            'total'
        ];
        foreach (BudgetHelper::getMonths() as $num => $monthName) {
            $arrayColumns[] = "month_{$num}";
        }

        ++$row;
        $count = count($this->getVisibleBudgetRows());

        foreach ($this->arResult['DATA'] as $data) {
            foreach ($data as $key => $value) {
                $column = $excelColumns[$key];
                if (empty($column)) {
                    continue;
                }

                if (in_array($key, $arrayColumns) and is_array($value)) {
                    $index = 0;
                    foreach ($value as $valueSum) {
                        $rowIndex = $row + $index;
                        ++$index;
                        $sheet->getCell("{$column['letter']}{$rowIndex}")->setValue($valueSum);
                    }

                    if ($count > 1) {
                        $rowIndex = $row + $count - 1;

                        foreach ($mergeColumns as $letter) {
                            $sheet->mergeCells("{$letter}{$row}:{$letter}{$rowIndex}");
                        }
                    }
                } else {
                    $sheet->getCell("{$column['letter']}{$row}")->setValue($value);
                }
            }

            if ($count > 1) {
                $row += $count;
            } else {
                ++$row;
            }
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode('123.xlsx').'"');
        $writer->save('php://output');
        Main\Application::getInstance()->end();
    }

    /**
     * @description Возвращает массив видимых строк бюджета
     * @return array
     */
    protected function getVisibleBudgetRows(): array
    {
        $filter = static::getFilterData($this->arResult['FILTER_ID']);
        if (empty($filter['budget'])) {
            $filter['budget'] = [];
        }

        array_unshift($filter['budget'], 'plan');

        $arBudgetRows = array_merge($this->arResult['BUDGET_ROWS']['HIDE'], $this->arResult['BUDGET_ROWS']['VISIBLE']);
        foreach ($arBudgetRows as $key => $name) {
            if (in_array($key, $filter['budget'])) {
                continue;
            }

            unset($arBudgetRows[$key]);
        }

        return $arBudgetRows ?? [];
    }

    /**
     * @description Подготовка данных после загрузки
     * @param array $inputData
     * @return array
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     */
    protected function prepareData(array $inputData = []): array
    {
        $arBudgetRows = $this->getVisibleBudgetRows();

        $arMonths = \Immo\Integration\Budget\BudgetHelper::getMonths();

        $arRows = [];
        foreach ($inputData as $hash => $data) {
            $arData = [
                'id' => $hash,
                'year' => $data['year'],
                'biznesUnit' => $data['beId'],
                'BE_NAME' => $data['biznesUnit'],
                'article' => $data['article'],
                'articleId' => $data['articleId'],
                'beId' => $data['beId'],
                'budget' => [],
                'total' => []
            ];

            foreach ($arBudgetRows as $key => $nameRow) {
                foreach ($arMonths as $monthNum => $name) {
                    $value = \Immo\Integration\Budget\BudgetHelper::formatNumber($data['data'][$monthNum][$key]);
                    $arData["month_{$monthNum}"][$key] = $value ?? 0;
                    if (!isset($value)) {
                        continue;
                    }

                    if (!array_key_exists($key, $arData['columns']['budget'])) {
                        $arData['budget'][$key] = $nameRow;
                        if (!array_key_exists($key, $arData['total'])) {
                            $data[$key] = \Immo\Integration\Budget\BudgetHelper::formatNumber($data[$key]);
                            $arData['total'][$key] = $data[$key] ?? 0;
                        }
                    }
                }
            }

            $arRows[] = $arData;
        }

        return $arRows ?? [];
    }

    /**
     * @description Вовзращает ID фильтра
     * @param string $extraParams
     * @return string
     */
    protected function getGridFilterId(string $extraParams = ''): string
    {
        return 'grid_budget_list_' . $extraParams;
    }

    /**
     * @return string[]
     */
    protected function listKeysSignedParameters(): array
    {
        return ['FILTER', 'DETAIL_ID'];
    }

    /**
     * @description Инициализция параметров таблицы
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function buildTableParams()
    {
        $this->arResult['BUDGET_ROWS'] = static::getBudgetRows();
        $fieldsFilter = static::getDefaultFilterColumns();
        if ($this->arParams['DETAIL'] == 'Y') {
            unset($fieldsFilter['biznesUnit']);
        }
        $this->arResult['FILTER_FIELDS'] = array_values($fieldsFilter);

        $arColumns = static::getColumns();
        if ($this->arParams['DETAIL'] == 'Y') {
            unset($arColumns['BE_NAME']);
        }
        $this->arResult['COLUMNS'] = array_values($arColumns);

        $this->arResult['FILTER_ID'] = $this->getGridFilterId((string)$this->arParams['DETAIL_ID']);
        $this->arResult['SIGNED_PARAMS'] = $this->getSignedParameters();

        if ($this->arParams['DETAIL'] == 'Y' and $this->arParams['DETAIL_ID'] > 0) {
            $arBe = Organization::getAllBe();
            foreach ($arBe as $be) {
                if ($be['OLD_ID'] != $this->arParams['DETAIL_ID']) {
                    continue;
                }

                $this->arResult['DETAIL_BE'] = $be;
                break;
            }
        }
    }

    /**
     * @description Достает и записывает в arResult данные из бюджета
     * @throws Main\ArgumentException
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function buildData()
    {
        $this->buildTableParams();

        $filter = Main\ORM\Query\Query::filter();
        if (!empty($this->arParams['FILTER'])) {
            $filter->where($this->arParams['FILTER']);
        }

        $this->arResult['DATA'] = $this->prepareData(static::loadBudgetGridFilter(
            $this->arResult['FILTER_ID'],
            ['*'],
            $this->arResult['FILTER_FIELDS'],
            $filter,
            true
        ));
        
        $arBe = Organization::getAllBe();
        if (!empty($arBe) and !empty($this->arResult['DATA'])) {
            foreach ($arBe as $be) {
                $arBeNames[$be['OLD_ID']] = "{$be['NAME']} ({$be['CURRENCY']})";
            }

            if (!empty($arBeNames)) {
                foreach ($this->arResult['DATA'] as $index => $data) {
                    if (!array_key_exists($data['biznesUnit'], $arBeNames)) {
                        continue;
                    }
                    
                    $this->arResult['DATA'][$index]['BE_NAME'] = $arBeNames[$data['biznesUnit']];
                }
            }
        }
    }
}
