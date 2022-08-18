<?php

namespace Immo\Statements\Data;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Main\UserFieldTable;
use Immo\Statements\Entity\FieldEnumTable;
use Immo\Statements\ModuleInterface;
use Immo\Statements\UserType\UserField;

Loader::includeModule('highloadblock');
Loc::loadMessages(__FILE__);

class HLBlock implements ModuleInterface
{
    public const UF_STATUS_XML_ID_ON_APPROVAL = 'on_approve';
    public const UF_STATUS_XML_ID_PAYEED = 'payeed';

    /**
     *  Название HL-блока
     * @var string
     */
    private string $name;

    /**
     * Массив с параметрами HL-блока (id, название, имя таблицы)
     * @var array
     */
    private array $hlBlock;

    private static $instance;

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function __construct(string $name)
    {
        $this->setName($name)
            ->setHlBlock();
    }

    /**
     * Создаёт объект класса без вызова конструктора
     *
     * @param string $name - название HL-блока
     *
     * @return HLBlock
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function createInstance(string $name): HLBlock
    {
        if(!(static::$instance instanceof static)) {
            static::$instance = new static($name);
        }

        return static::$instance;
    }

    /**
     * Получает название HL-блока по ID
     * М.б. использовано в миграциях
     *
     * @param $id - ID HL-блока
     *
     * @return string
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getNameById($id): string
    {
        return HighloadBlockTable::getById($id)->fetchObject()->getName();
    }

    /**
     * @param string $name
     * @return HLBlock
     */
    public function setName(string $name): HLBlock
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает HL-блок по названию
     *
     * @return HLBlock
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function setHlBlock(): HLBlock
    {
        $this->hlBlock = HighloadBlockTable::getRow([
            'filter' => ['NAME' => $this->name]
        ]);

        return $this;
    }

    /**
     * Возвращает массив HL-блока
     *
     * @return array
     */
    public function getHlBlock(): array
    {
        return $this->hlBlock;
    }

    /**
     * Возвращает id HL-блока
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->hlBlock['ID'];
    }

    /**
     * Возвращает сущность HL-блока
     *
     * @return DataManager|string
     *
     * @throws SystemException
     */
    public function getEntity()
    {
        return HighloadBlockTable::compileEntity($this->getId())->getDataClass();
    }

    /**
     * Возвращает название сущности для получения списка полей HL-блока
     *
     * @return string
     */
    public function getUfEntityId(): string
    {
        return sprintf('HLBLOCK_%d', $this->getId());
    }

    /**
     * Возвращает список полей HL-блока
     *
     * @param array $filter
     *
     * @return array
     *
     */
    public function getFields(array $filter = []): array
    {
        $fields = [
            'ID' => 'Порядковый номер'
        ];

        $fieldName = '';

        if(isset($filter['FIELD_NAME'])) {
            $fieldName = $filter['FIELD_NAME'];
        }

        $filter['ENTITY_ID'] = $this->getUfEntityId();

        $params = [
            'filter' => $filter,
        ];

        $list = UserField::list($params);

        return $fieldName !== '' ? $list[$fieldName] : array_merge($fields, $list);
    }

    /**
     * Возвращает список элементов HL-блока.
     * Может использовать фильтрацию
     *
     * @param array $options
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getElements(array &$options = []): array
    {
        $class = $this->getEntity();
        $fields = array_keys($this->getFields());

        $options['select'] = !is_null($options['select']) ? array_merge($options['select'], $fields) : $fields;

        return $class::getList($options)->fetchAll();
    }

    /**
     * Возвращает id элемента HL-блока по роли и ID бизнес-процесса
     *
     * @param $role
     * @param $iblockId
     *
     * @return int
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getElementIdByRole($role, $iblockId = null): int
    {
        $options = [
            'filter' => [
                'UF_ROLES' => $role,
            ]
        ];

        if(!is_null($iblockId)) {
            $options['filter']['UF_BP_IBLOCK_ID'] = $iblockId;
        }

        $hl = $this->getElements($options);

        return (int) $hl['ID'];
    }

    /**
     * Возвращает id элемента ИБ ЗПВ по id hl-элемента
     *
     * @param $rowId
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getLabelsSalaryElementId($rowId)
    {
        $options = [
            'select' => ['ID', 'ELEMENT_ID' => 'UF_LABELS_SALARY_ELEMENT_ID']
        ];

        $row = $this->getEntity()::getByPrimary($rowId, $options)->fetch();
        return (int) $row['ELEMENT_ID'];
    }
}