<?php

namespace Immo\Statements\Grid;

use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Query\Join;
use Immo\Statements\Data\Department;
use Immo\Statements\Data\HLBlock;
use Immo\Statements\Data\Iblock;
use Immo\Statements\Entity\FieldEnumTable;
use Immo\Statements\Helpers\GridHelper;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\ModuleTrait;
use Immo\Statements\UserType\UserField;

class Grid extends AbstractGrid implements ModuleInterface
{
    use ModuleTrait;

    private int $iblockId;
    private string $viewMode;
    private string $hlEntityName;
    private HLBlock $hlBlock;

    public function __construct($iblockId, $viewMode, $hlEntityName = null)
    {
        $this->iblockId = $iblockId;
        $this->viewMode = $viewMode;
        
        $this->hlEntityName = $hlEntityName ?? self::HL_ENTITY_STATEMENTS_APPROVAL;
        $this->hlBlock = HLBlock::createInstance($this->hlEntityName);
    }

    /**
     * Возвращает список полей для грида в соответствии с ролью пользователя
     *
     * @return array
     */
    public function getColumns(): array
    {
        $fields = Fields::getFieldsByViewMode($this->viewMode);
        array_unshift($fields, 'ID');

        return $this->prepareFields($fields);

    }

    /**
     * Возвращает список строк для грида
     *
     * @return array
     */
    public function getRows($filter = []): array
    {
        $rows = [];

        $fields = Fields::getFieldsByViewMode($this->viewMode);
        array_unshift($fields, 'ID');
        $fields[] = 'SELECTED_UR';

        $entity = Iblock::getEntity($this->iblockId);
        $ufStatus = $this->hlBlock->getFields(['FIELD_NAME' => 'UF_STATUS']);

        $params = [
            'filter' => $filter,
            'select' => $this->prepareSelectFields($fields),
        ];

        $list = $entity::getList($params)->fetchAll();
        $viewMode = $this->viewMode === 'BE' ? 'UR': $this->viewMode;

        foreach ($list as $item) {
            $rowId = (int) $item['ID'];
            $rows[$rowId] = [
                'id' => $item['ID'],
                'columns' => [
                    'ID' => $item['ID']
                ]
            ];

            foreach ($fields as $field) {
                $preparedField = sprintf('%s_VALUE', $field);

                if (!empty($item[$preparedField])) {
                    switch ($field) {
                        case 'SELECTED_BE':
                        case 'SELECTED_UR':
                            $valueId = (int) $item[$preparedField];
                            $value = Department::getElementNameById($valueId);
                            $url = sprintf('/sheets/salary/detail/%d/', $rowId);

                            $rows[$rowId]['columns'][$field] = print_url($url, $value);
                            break;
                        case 'STATUS_CARD':
                            $entityOptions = [
                                'filter' => [
                                    'UF_LABELS_SALARY_ELEMENT_ID' => $rowId
                                ]
                            ];
                            $hlElements = $this->hlBlock->getElements($entityOptions);
                            $hlUfStatusValues = array_column($hlElements, 'UF_STATUS');

                            foreach ($ufStatus['items'] as $ufValueId => $ufData) {
                                if(
                                    in_array($ufValueId, $hlUfStatusValues) &&
                                    $ufData['xml_id'] === HLBlock::UF_STATUS_XML_ID_ON_APPROVAL
                                ) {
                                    $rows[$rowId]['columns'][$field] = $ufData['value'];
                                } elseif(
                                    !in_array($ufValueId, $hlUfStatusValues) &&
                                    $ufData['xml_id'] === HLBlock::UF_STATUS_XML_ID_ON_APPROVAL
                                ) {
                                    $enumValue = UserField::getUfEnumValue('UF_STATUS', HLBlock::UF_STATUS_XML_ID_PAYEED);
                                    $rows[$rowId]['columns'][$field] = $enumValue['VALUE'];
                                }
                            }

                            break;
                        case 'F_MONTH':
                            $valueId = (int) $item[$preparedField];
                            $value = Iblock::getPropertiesListValues($field, $this->iblockId, $valueId);
                            $rows[$rowId]['columns'][$field] = $value[$valueId];
                            break;
                        case 'F_YEAR':
                            $elementId = (int)$item[$preparedField];
                            $value = Iblock::getPropertyFinancialYearsList($this->iblockId, $elementId);
                            $rows[$rowId]['columns'][$field] = $value[$elementId];
                            break;
                        default:
                            $rows[$rowId]['columns'][$field] = $item[$preparedField];
                            break;

                    }
                }
            }
        }

        return array_values($rows);
    }

    /**
     * Подготавливает массив с описаниями полей для грида
     *
     * @param $fields
     * @return array
     */
    public function prepareFields($fields): array
    {
        $results = [];
        $ufStatus = $this->hlBlock->getFields([
            'FIELD_NAME' => 'UF_STATUS'
        ]);


        foreach ($fields as $field) {
            
            $name = '';
            
            switch ($field) {
                case 'ID':
                    $name = $field;
                    break;
                case 'STATUS_CARD':
                    $name = $ufStatus['label'];
                    break;
                default:
                    $name = Fields::getPropertyName($field, $this->iblockId);
                    break;

            }
            
            $results[] = [
                'id' => $field,
                'name' => $name,
                'sort' => $field,
                'default' => !($field === 'ID')
            ];
        }

        return $results;
    }


}