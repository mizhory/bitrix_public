<?php

namespace Immo\Statements\Details;

use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Grid\Types;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Immo\Statements\Calculator\SalaryStatementCalculator;
use Immo\Statements\Calculator\StatementsCalculatorInterface;
use Immo\Statements\Data\Department;
use Immo\Statements\Data\HLBlock;
use Immo\Statements\Grid\AbstractGrid;
use Immo\Statements\Helpers\GridHelper;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\ModuleTrait;
use ReflectionException;

class Grid extends AbstractGrid implements ModuleInterface
{
    use ModuleTrait;

    private string $role;
    private HLBlock $entity;

    /**
     */
    public function __construct(string $role, HLBlock $entity)
    {
        $this->role = $role;
        $this->entity = $entity;


    }

    /**
     * Подготавливает столбцы для грида
     *
     * @param $fields
     * @return array
     */
    public function prepareFields($fields): array
    {
        $results = [];

        foreach ($fields as $field => $data) {
            $results[] = [
                'id' => $field,
                'name' => $data['label'],
                'default' => true,
                'sort' => false
            ];
        }

        return $results;
    }

    /**
     * Возвращает список столбцов для грида
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getColumns(): array
    {
        $fields = Fields::getFieldsByRole($this->role, $this->entity);

        return $this->prepareFields($fields);
    }

    /**
     * Возвращает список строк для грида
     *
     * @param array $filter
     * @param array $order
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws ReflectionException
     * @throws SystemException
     */
    public function getRows(array $filter = [], array $order = []): array
    {
        $rows = [];
        $fields = Fields::getFieldsByRole($this->role, $this->entity);

        $entityOptions = [
            'filter' => $filter,
            'order' => $order,
            'runtime' => [
                new Reference('USER', UserTable::class, Join::on('this.UF_USER', 'ref.ID'))
            ],
            'select' => ['UF_USER', 'UF_CS_BE' => 'USER.UF_CS_BE']
        ];

        $elements = $this->entity->getElements($entityOptions);
        $num = 0;

        foreach ($elements as $element) {
            $rowId = $element['ID'];
            $beId = $element['UF_BE'];
            $beCurrency = Department::getDepartmentCurrency($beId);
            $user = UserTable::getById($element['UF_USER'])->fetchObject();

            $rows[$rowId] = [
                'id' => $rowId,
                'data' => [
                    'id' => $rowId,
                    'be_currency' => $beCurrency
                ],
            ];



            foreach ($fields as $field => $data) {


                $options = [
                    'field' => $field
                ];

                switch ($data['type']) {
                    case 'iblock_section':
                        $options['value'] = Department::getElementNameById($element[$field]);
                        break;
                    case 'employee':
                        $options['value'] = sprintf('%s %s', $user->getName(), $user->getLastName());
                        break;
                    case 'money':
                        $value = $data['default_is_empty'] && empty($element[$field]) ? explode('|', $element[$data['default_is_empty']]) : explode('|', $element[$field]);
                        $rows[$rowId]['data'][$field] = $value[0];
                        $options['value'] = sprintf('%s %s', $value[0], $value[1]);

                        if($data['editable']) {
                            $options['page'] = 'input';
                            $options['row_id'] = $rowId;
                            $options['step'] = $data['field_type'] === Fields::FIELD_TYPE_NUMBER ? '0.01' : '';
                            $options['input_type'] = $data['field_type'];
                            $options['value'] = number_format((int)$value[0], 2, ',', ' ');
                        } elseif(
                            ($data['calculated'] || $data['calculated_editable']) &&
                            is_subclass_of($data['callback_class'], StatementsCalculatorInterface::class)
                        ) {
                            $callback = Fields::getCallback($data['callback_class'], $data['callback_method'], $element);
                            $value = $callback();
                            $rows[$rowId]['data'][$field] = $value;

                            if($data['field_type']) {
                                $options['input_type'] = $data['field_type'];
                            }

                            $options['value'] = $data['calculated_editable'] ? $value : sprintf('%s %s',$value, $beCurrency);
                        }
                        break;
                    case 'enumeration':
                        $options['page'] = 'select';
                        $options['row_id'] = $rowId;
                        $options['input_type'] = Fields::FIELD_TYPE_SELECT;
                        $options['value'] = $element[$field];
                        $options['items'] = $data['items'];
                        break;
                    default:
                        if($data['editable']) {
                            $options['page'] = 'input';
                            $options['row_id'] = $rowId;
                        }

                        $options['value'] = $element[$field];
                }

                $rows[$rowId]['columns'][$field] = $field === 'ID' ? ++$num : GridHelper::prepareGridCell($options);
            }
        }

        $this->prepareFinalRow($rows);
        return $rows;
    }

    /**
     * Подготавливает финальную строку
     *
     * @param array $rows
     * @return array
     */
    public function prepareFinalRow(array &$rows): array
    {
        $fields = $this->entity->getFields();
        $finalRow = [];
        foreach ($rows as $elementId => $row) {

            $beCurrency = $row['data']['be_currency'];

            foreach ($row['columns'] as $field => $value) {
                $finalRow['columnClasses'][$field] = 'final-row';

                switch ($field) {
                    case 'ID':
                        break;
                    case 'UF_USER':
                        $finalRow['columns'][$field] = count($rows);
                        break;
                    case 'TOTAL_ON_HANDS':
                        $this->calculateTotalSum($rows, $field, $finalRow, $beCurrency);
                        break;
                    default:
                        if(array_key_exists($field, $fields) && $fields[$field]['type'] === MoneyType::USER_TYPE_ID) {
                            $this->calculateTotalSum($rows, $field, $finalRow, $beCurrency);
                        }
                        break;
                }
            }
        }

        $rows[] = $finalRow;

        return $rows;
    }

    /**
     * Вычисляет сумму всех строк в столбце
     *
     * @param $rows
     * @param $field
     * @param $finalRow
     * @param $beCurrency
     * @return array
     */
    public function calculateTotalSum(&$rows, $field, &$finalRow, $beCurrency): array
    {
        $data = array_column($rows, 'data');

        if(!empty(array_column($data, $field))) {
            $sum = (float) array_sum(array_column($data, $field));
            $finalRow['columns'][$field] = sprintf('%s %s', $sum, $beCurrency);
        }

        return $finalRow;
    }

    /**
     * Подготавливает ключ для отображения типа валюты в итоговой строке
     *
     * @param $field
     * @return string
     */
    protected function prepareFieldCurrencyKey($field): string
    {
        return sprintf('%s_CURRENCY', $field);
    }
}