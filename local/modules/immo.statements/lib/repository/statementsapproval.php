<?php

namespace Immo\Statements\Repository;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Immo\Statements\Data\HLBlock;
use Immo\Statements\ModuleInterface;
use Bitrix\Main\UserFieldTable;

/**
 * Для работы с списком HLблоком Ведомости на согласовании (StatementsApproval)
 */
class StatementsApproval implements ModuleInterface
{
    private HLBlock $obHlBlockStatements;
    /**
     * @var \Bitrix\Main\ORM\Data\DataManager|string
     */
    private $sClassStatements;

    private static ?string $sClassName = null;
    private static ?StatementsApproval $obStatementsApproval = null;

    /**
     * @throws \Bitrix\Main\SystemException
     */
    private function __construct()
    {
        $this->obHlBlockStatements = new HLBlock(static::HL_ENTITY_STATEMENTS_APPROVAL);
        $this->sClassStatements = $this->obHlBlockStatements->getEntity();
    }

    public static function getInstance(): StatementsApproval
    {
        if (self::$obStatementsApproval === null) {

            self::$obStatementsApproval = new static();
        }
        return self::$obStatementsApproval;
    }

    /**
     * @return string
     */
    private function getUfEntityId()
    {
        return $this->obHlBlockStatements->getUfEntityId();
    }

    /**
     * @return \Bitrix\Main\ORM\Data\DataManager|string
     */
    public function getClassNameHlBlock()
    {
        return $this->sClassStatements;
    }

    /**
     * @throws \Bitrix\Main\SystemException
     */
    private static function getClassName()
    {
        if (self::$sClassName === null) {
            self::$sClassName = self::getInstance()->getClassNameHlBlock();
        }
        return self::$sClassName;
    }

    /**
     * @param array $parameters
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getList(array $parameters = [])
    {
        return self::getClassName()::getList($parameters);
    }

    /**
     * Создание
     * @param array $data
     * @return \Bitrix\Main\ORM\Data\AddResult
     * @throws \Bitrix\Main\SystemException
     */
    public static function create(array $data)
    {
        return self::getClassName()::add($data);
    }


    private static function update($primary, array $data)
    {
        return self::getClassName()::update($primary, $data);
    }

    /**
     * @param $primaries
     * @param $data
     * @param bool $ignoreEvents
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function updateMulti($primaries, $data, $ignoreEvents = false)
    {
        return self::getClassName()::updateMulti($primaries, $data, $ignoreEvents);
    }

    /**
     * Количество пользователей в ЗПВ
     * <code>
     * $ob = new \Immo\Statements\Repository\StatementsApproval();
     * $ob->getCountUserByDate(2022, '04');
     * </code>
     * @param int $iYear - год
     * @param string $sMonthXmlId - xml id  значения enum списка
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCountUserByDate(int $iYear, string $sMonthXmlId): int
    {
        $obRes = self::getList(
            [
                'filter' => [
                    '=UF_MONTH' => $this->getMonthEnumPropertyValueId($sMonthXmlId),
                    '=UF_YEAR' => $iYear
                ],
                'select' => [
                    'UF_USER',
                    'cntStatement'
                ],
                'group' => [
                    'UF_USER'
                ],
                'runtime' => [
                    new \Bitrix\Main\Entity\ExpressionField(
                        'cntStatement',
                        'count(%s)',
                        'UF_USER'
                    )

                ]
            ]
        );
        $iCount = 0;
        while ($arStatement = $obRes->fetch()) {
            $iCount += $arStatement['cntStatement'];
        }
        return $iCount;
    }

    /**
     * Количество ярлыков ЗПВ в статусе и по дате
     * @param int $iYear
     * @param string $sMonthXmlId
     * @param string $sStatusCartXmlId
     * @return int|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCountStatement(int $iYear, string $sMonthXmlId, string $sStatusCartXmlId): int
    {
        $obRes = self::getList(
            [
                'filter' => [
                    '=UF_MONTH' => $this->getMonthEnumPropertyValueId($sMonthXmlId),
                    '=UF_STATUS_CARD' => $this->getStatusCartEnumIdByValue($sStatusCartXmlId),
                    '=UF_YEAR' => $iYear,
                ],
                'select' => [
                    'UF_USER',
                    'cntStatement'
                ],
                'runtime' => [
                    new \Bitrix\Main\Entity\ExpressionField(
                        'cntStatement',
                        'count(%s)',
                        'UF_USER'
                    )

                ]
            ]
        );
        $iCount = 0;
        while ($arStatement = $obRes->fetch()) {
            $iCount += $arStatement['cntStatement'];
        }
        return $iCount;
    }

    /**
     * ID enum свойства Месяца
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getMonthEnumPropertyValueId(string $sMonthXmlId): int
    {
        $arEnums = $this->getMonthEnumPropertyValues();
        if (array_key_exists($sMonthXmlId, $arEnums)) {
            return $arEnums[$sMonthXmlId]['ID'];
        }
        return 0;
    }

    /**
     * Получить xml_id по назавнию месяца
     * @param string $sMonthName
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getMonthXmlIdByValue(string $sMonthName): string
    {
        $sMonthCode = '';
        $arEnums = $this->getMonthEnumPropertyValues();
        foreach ($arEnums as $arEnumValue) {
            if ($arEnumValue['VALUE'] === $sMonthName) {
                $sMonthCode = $arEnumValue['XML_ID'];
                break;
            }
        }
        return $sMonthCode;
    }

    /**
     * Получить enum_id по назавнию месяца
     * @param string $sMonthName
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getMonthEnumIdByValue(string $sMonthName): string
    {
        $iMonthId = '';
        $arEnums = $this->getMonthEnumPropertyValues();
        foreach ($arEnums as $arEnumValue) {
            if ($arEnumValue['VALUE'] === $sMonthName) {
                $iMonthId = $arEnumValue['ID'];
                break;
            }
        }
        return $iMonthId;
    }

    /**
     * ID enum свойства Статус согласования[UF_STATUS_CARD]
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getStatusCartEnumIdByValue(string $sMonthXmlId): int
    {
        $arEnums = $this->getStatusCartEnumPropertyValues();
        if (array_key_exists($sMonthXmlId, $arEnums)) {
            return $arEnums[$sMonthXmlId]['ID'];
        }
        return 0;
    }

    /**
     * Enum значения свойства Месяц[UF_MONTH]
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getMonthEnumPropertyValues(): array
    {
        return $this->getEnumPropertyValues('UF_MONTH');
    }

    /**
     * Enum значения свойства Статус согласования[UF_STATUS_CARD]
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getStatusCartEnumPropertyValues(): array
    {
        return $this->getEnumPropertyValues('UF_STATUS_CARD');
    }

    /**
     * Enum значения свойств
     * @param string $sFieldName
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getEnumPropertyValues(string $sFieldName): array
    {
        static $arEnums;
        if ($arEnums[$sFieldName] === null) {
            $arEnums[$sFieldName] = [];
            $entityId = self::getInstance()->getUfEntityId();
            $arField = UserFieldTable::query()
                ->where([
                    ['ENTITY_ID', $entityId],
                    ['FIELD_NAME', $sFieldName]
                ])
                ->addSelect('ID')
                ->exec()
                ->fetch();
            if (empty($arField)) {
                return [];
            }
            $rsFieldEnums = (new \CUserFieldEnum())->GetList([], ['USER_FIELD_ID' => $arField['ID']]);
            while ($enum = $rsFieldEnums->Fetch()) {
                $arEnums[$sFieldName][$enum['XML_ID']] = $enum;
            }
        }
        return $arEnums[$sFieldName];
    }

    /**
     * Получить ЯЗПВ сгруппированых по БЕ и Типу бланка
     * @param int $iYear
     * @param int $iMonthEnumId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getStatementsByDate(int $iYear, int $iMonthEnumId): array
    {
        $resStatements = self::getList(
            [
                'filter' => [
                    '=UF_YEAR' => $iYear,
                    '=UF_MONTH' => $iMonthEnumId,
                ],
                'select' => [
                    'ID',
                    'UF_BE',
                    'UF_USER_TYPE',
                ]
            ]
        );
        /*
         * Группировка ЯЗПВ по UF_BE
         * [
         *      БЕ_1 => [
         *          ТИП БЛАНКА 1 => [ЯЗПВ_ID, ЯЗПВ_ID, ЯЗПВ_ID...],
         *          ТИП БЛАНКА 2 => [ЯЗПВ_ID, ЯЗПВ_ID, ЯЗПВ_ID...],
         *      ]
         * ]
         */
        $arResult = [];
        while ($arStatements = $resStatements->fetch()) {
            if (!$arStatements['UF_USER_TYPE']) {
                $arStatements['UF_USER_TYPE'] = TypeUser::getDefaultTypeId();
            }
            $arResult[$arStatements['UF_BE']][$arStatements['UF_USER_TYPE']][] = $arStatements['ID'];
        }
        return $arResult;
    }

    /**
     * ID пользователей по дате ЗПВ
     * @param int $iYear
     * @param int $iMonthEnumId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getUserIdsStatementByDate(int $iYear, int $iMonthEnumId): array
    {
        $resStatement = self::getList(
            [
                'filter' => [
                    '=UF_YEAR' => $iYear,
                    '=UF_MONTH' => $iMonthEnumId,
                ],
                'select' => [
                    'UF_USER',
                ]
            ]
        );
        $arUserIds = [];
        while ($arStatement = $resStatement->fetch()) {
            $arUserIds[] = $arStatement['UF_USER'];
        }
        return $arUserIds;
    }

    /**
     * Создание ЯЗПВ для внештатных сотрудников
     * @param array $arFields
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function generationFreelanceEmployee(array $arFields)
    {
        $arFieldsForSave = [
            'UF_STATUS_CARD' => (int)$this->getStatusCartEnumIdByValue('direcBe'), // статус стартовый

            'UF_BE' => (int)$arFields['newIdSalaryBeSelect'], // ID БЕ НОВЫЙ (ID раздела оргструктуры)
            'UF_USER' => $arFields['ID'], // ID пользователя
            'UF_USER_TYPE' => $arFields['UF_USER_TYPE'], // ID типа пользователя
            'UF_MONTH' => $arFields['UF_MONTH'], // ID значения списка месяца
            'UF_YEAR' => $arFields['UF_YEAR'], // год
            'UF_OFFER_SUM' => round($arFields['UF_CS_BE']['salary'], 2), // Из пользователя: Оклад по офферу
            'UF_OVERPAYMENTS' => round($arFields['UF_CS_BE']['overSalary'], 2), // Из пользователя: Переплаты
            'UF_1C_SUM' => round($arFields['UF_SALARY_TOTAL'], 2), // Из хайлода: Данные по сотрудникам в юрлицах
            'UF_CURRENCY' => $this->defineCurrencyByBusinessUnit((int)$arFields['newIdSalaryBeSelect']), // Валюта по БЕ
        ];
        static::create($arFieldsForSave);
    }

    /**
     * Получение валюды БЕ
     * @param int $iBusinessUnit
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function defineCurrencyByBusinessUnit(int $iBusinessUnit): int
    {
        static $arCurrencyByBusinessUnit = [];
        if (array_key_exists($iBusinessUnit, $arCurrencyByBusinessUnit)) {
            return $arCurrencyByBusinessUnit[$iBusinessUnit];
        }
        $arBe = \Immo\Statements\Generation\Helper::getStructure();
        if (array_key_exists($iBusinessUnit, $arBe)) {
            $arBusinessUnit = $arBe[$iBusinessUnit];
            $iCountry = (int)$arBusinessUnit['BE']['UF_COUNTRY'];
            $arCurrencyByBusinessUnit[$iBusinessUnit] = \Immo\Statements\Generation\Helper::defineCurrencyByCountry($iCountry);
        }
        return $arCurrencyByBusinessUnit[$iBusinessUnit];
    }

    /**
     * устанавливается порядковый номер для ЗП ведомсти
     * @param int $id ID зарплатной ведомости
     * @return void
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function generateSortForBusinessUnit(int $id): void
    {
        // выбор ЯЗПВ по ЗПВ
        $arFilter = [
            '=UF_LABELS_SALARY_ELEMENT_ID' => $id
        ];
        $arOptions = [
            'filter' => $arFilter,
            'select' => [
                'ID', 'UF_BE', 'UF_USER'
            ],
            'runtime' => [
                new Reference(
                    'USER',
                    UserTable::class,
                    Join::on('this.UF_USER', 'ref.ID')
                )
            ],
            // сортировака пользователей
            'order' => [
                'USER.LAST_NAME' => 'asc',
                'USER.NAME' => 'asc',
                'USER.SECOND_NAME' => 'asc',
            ]
        ];
        $obResult = static::getList($arOptions);
        // группировка пользвоателей по БЕ
        $arGroupUsersByBe = [];
        while ($arElem = $obResult->fetch()) {
            $arGroupUsersByBe[$arElem['UF_BE']][] = $arElem;
        }
        if (!$arGroupUsersByBe) {
            return;
        }
        // установка сортировки
        foreach ($arGroupUsersByBe as $arUsers) {
            $count = 1;
            foreach ($arUsers as $arUser) {
                static::update(
                    $arUser['ID'],
                    [
                        'UF_SORT' => $count
                    ]
                );
                $count++;
            }
        }
    }
}