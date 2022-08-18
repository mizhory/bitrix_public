<?php

namespace Immo\Statements\Helpers;

use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Context;
use Bitrix\Main\Grid\Types;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Immo\Statements\Data\Iblock;
use Immo\Statements\Grid\Fields;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\ModuleTrait;

class GridHelper implements ModuleInterface
{

    use ModuleTrait;

    /**
     * Возвращает список элемента ЗПВ по БЕ/ЮЛ
     *
     * @param $type - тип (БЕ/ЮЛ)
     * @param $itemId - ID БЕ/ЮЛ
     *
     * @return array
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getItemsByType($type, $itemId)
    {
        $items = [];
        $iblockId = Iblock::getIblockId(self::IBLOCK_CODE_LABELS_SALARY, self::IBLOCK_TYPE_BITRIX_PROCESSES);
        /**
         * @var DataManager $entity
         */
        $entity = Iblock::getEntity($iblockId);
        $viewMode = $type === 'company' ? 'UR' : 'BE';
        $filterField = sprintf('SELECTED_%s.VALUE', $viewMode);
        $select = ['ID'];

        $params = [
            'select' => $select,
            'filter' => [
                $filterField => $itemId
            ],

        ];

        foreach ($entity::getList($params)->fetchAll() as $item) {
            $items[] = (int) $item['ID'];
        }

        return $items;
    }

    /**
     * Возвращает представление отдельной ячейки.
     * Параметры массива:
     *
     * value - основное значение ячейки
     * page - страница шаблона. Допустимые значения: link, input, select. Если отсутствует, будет открыта дефолтная страница
     * action - ссылка для формы (на странице link)
     * post_data - массив с данными, передаваемыми по ссылке (на странице link). Необязательный параметр
     * input_type - тип инпута (для страниц input, select). Значения есть в классе Immo\Statements\Details\Fields, константы FIELD_TYPE_*
     * field - символьный код поля (для страниц input, select)
     * row_id - id HL-элемента (для страниц input, select)
     *
     * @param $options - массив, передаваемый в компонент
     *
     * @return false|string
     */
    public static function prepareGridCell($options)
    {
        ob_start();

        self::getApplication()->IncludeComponent('immo:grid.cell', '', $options);

        return ob_get_clean();
    }
}