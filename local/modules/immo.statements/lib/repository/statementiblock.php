<?php

namespace Immo\Statements\Repository;

use CIBlockElement;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\IblockTrait;

/**
 * Для работы ос списком Зарплатная ведомость
 */
class StatementIblock implements ModuleInterface
{
    use IblockTrait;

    private static ?StatementIblock $obStatementIblock = null;
    private string $sIblockCode;
    private string $sIblockTypeCode;
    private int $iIblockId;
    private ?array $arMonthList = null;

    private function __construct()
    {
        $this->sIblockCode = static::IBLOCK_CODE_LABELS_SALARY;
        $this->sIblockTypeCode = static::IBLOCK_TYPE_BITRIX_PROCESSES;
        $this->iIblockId = static::getIblockId($this->sIblockCode, $this->sIblockTypeCode);
    }

    /**
     * Создание объекта для работы со списками
     * @return StatementIblock
     */
    public static function getInstance()
    {
        if (self::$obStatementIblock === null) {
            self::$obStatementIblock = new self();
        }
        return self::$obStatementIblock;
    }

    /**
     * Id инфоблока
     * @return int
     */
    public function getIblock(): int
    {
        return $this->iIblockId;
    }

    /**
     * Список Enum свойств
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getStatusCardList(): array
    {
        if ($this->arMonthList === null) {
            $this->arMonthList = IblockTrait::getPropertiesListValues(
                'STATUS_CARD',
                $this->getIblock(),
                null,
                true
            );
        }
        return $this->arMonthList;
    }

    /**
     * Получть XML_ID Статус согласования по ENUM_ID
     * @param int $iStatusCardEnumId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getStatusCardXmlIdByEnumId(int $iStatusCardEnumId): string
    {
        $sXmlIdStatusCard = '';
        $arEnumValues = $this->getStatusCardList();
        if (array_key_exists($iStatusCardEnumId, $arEnumValues)) {
            $sXmlIdStatusCard = $arEnumValues[$iStatusCardEnumId]['xml_id'];
        }
        return $sXmlIdStatusCard;
    }

    /**
     * Список enum свойства Месяц
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getMonthList(): array
    {
        if ($this->arMonthList === null) {
            $this->arMonthList = IblockTrait::getPropertiesListValues(
                'F_MONTH',
                $this->getIblock(),
                null,
                true
            );
        }
        return $this->arMonthList;
    }

    /**
     * Получить xml_id по назавнию месяца
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getMonthXmlIdByValue(string $sMonthName): string
    {
        $arEnumValues = $this->getMonthList();
        $sMonthCode = '';
        foreach ($arEnumValues as $arEnumValue) {
            if ($arEnumValue['value'] === $sMonthName) {
                $sMonthCode = $arEnumValue['xml_id'];
                break;
            }
        }
        return $sMonthCode;
    }

    /**
     * Получить enum_id по назавнию месяца
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getMonthEnumIdByValue(string $sMonthName): int
    {
        $arEnumValues = $this->getMonthList();
        $iMonthEnumId = 0;
        foreach ($arEnumValues as $arEnumValue) {
            if ($arEnumValue['value'] === $sMonthName) {
                $iMonthEnumId = $arEnumValue['id'];
                break;
            }
        }
        return $iMonthEnumId;
    }

    /**
     * Для работы с элементами
     * @param $arOrder
     * @param $arFilter
     * @param $arGroupBy
     * @param $arNavStartParams
     * @param $arSelectFields
     * @return \CIBlockResult|int
     */
    public static function GetList($arOrder = ["SORT" => "ASC"], $arFilter = [], $arGroupBy = false, $arNavStartParams = false, $arSelectFields = [])
    {
        $obStatementIblock = self::getInstance();
        $arFilter['IBLOCK_ID'] = $obStatementIblock->getIblock();
        return CIBlockElement::GetList(
            $arOrder
            , $arFilter
            , $arGroupBy
            , $arNavStartParams
            , $arSelectFields
        );
    }

    /**
     * Поиск ЗПВ при генерации по БЕ
     * @param int $iBusinessUnitId
     * @param int $iUserTypeId
     * @param int $iYear
     * @param int $iMonthEnumId
     * @return int
     */
    private function findStatementIdInGenerationsForBusinessUnit(int $iBusinessUnitId, int $iUserTypeId, int $iYear, int $iMonthEnumId): int
    {
        static $StatementIdByBusinessUnit = [];
        $id = 0;
        $arFilter = [
            '=PROPERTY_' . 'SELECTED_BE' => $iBusinessUnitId,
            '=PROPERTY_' . 'TYPE_USER' => $iUserTypeId,
            '=PROPERTY_' . 'F_YEAR' => $iYear,
            '=PROPERTY_' . 'F_MONTH' => $iMonthEnumId,
            '=PROPERTY_' . 'SELECTED_UR' => false,
        ];
        $sCache = md5(serialize($arFilter));
        if (!array_key_exists($sCache, $StatementIdByBusinessUnit)) {
            $resStatement = self::GetList(
                [],
                $arFilter,
                false,
                false,
                ['ID']
            );
            if ($arStatement = $resStatement->Fetch()) {
                $id = $arStatement['ID'];
            }else{
                $arFields = [
                    'SELECTED_BE' => $iBusinessUnitId,
                    'TYPE_USER' => $iUserTypeId,
                    'F_YEAR' => $iYear, // ??
                    'F_MONTH' => $iMonthEnumId,
                ];
                $id = $this->create($arFields);
            }
            $StatementIdByBusinessUnit[$sCache] = $id;
        }
        return $StatementIdByBusinessUnit[$sCache];
    }

    /**
     * Создание ЗПВ по БЕ и возвращает какие ЯЗПВ относятся к новым ЗПВ
     * @param array $arStatementsApprovalGroup
     * @param int $iYear
     * @param int $iMonthEnumId
     * @return void
     */
    public function generateStatements(array $arStatementsApprovalGroup, int $iYear, int $iMonthEnumId): array
    {
        /*
         * $arStatementsApprovalGroup = [
         *      БЕ_1 => [
         *          ТИП БЛАНКА 1 => [ЯЗПВ_ID, ЯЗПВ_ID, ЯЗПВ_ID...],
         *          ТИП БЛАНКА 2 => [ЯЗПВ_ID, ЯЗПВ_ID, ЯЗПВ_ID...],
         *      ]
         * ]
         */
        $arResult = [];
        foreach ($arStatementsApprovalGroup as $iBusinessUnitId => $arStatements) {
            // создание ЗПВ для БЕ
            foreach ($arStatements as $iUserTypeId => $iStatementsApprovalId) {

                $iStatementId = $this->findStatementIdInGenerationsForBusinessUnit(
                    $iBusinessUnitId,
                    $iUserTypeId,
                    $iYear,
                    $iMonthEnumId
                );

                $arResult[$iStatementId] = $iStatementsApprovalId;
            }
        }
        /*
         * $arResult = [
         *      БЕ_1 => [ЯЗПВ_ID...]
         * ]
         */
        return $arResult;
    }

    public function create(array $arFields): int
    {

        $provider = new CIBlockElement();
        $arProps = [];
        if ($arFields['SELECTED_BE']) {
            $arProps['SELECTED_BE'] = $arFields['SELECTED_BE'];
        }
        if ($arFields['SELECTED_UR']) {
            $arProps['SELECTED_UR'] = $arFields['SELECTED_UR'];
        }
        if ($arFields['F_YEAR']) {
            $arProps['F_YEAR'] = $arFields['F_YEAR'];
        }

        if ($arFields['F_MONTH']) {
            $arProps['F_MONTH'] = $arFields['F_MONTH'];
        }
        if ($arFields['STATUS_CARD']) {
            $arProps['STATUS_CARD'] = $arFields['STATUS_CARD'];
        }
        if ($arFields['TYPE_USER']) {
            $arProps['TYPE_USER'] = $arFields['TYPE_USER'];
        }
        if ($arFields['TYPE_STATEMENT']) {
            $arProps['TYPE_STATEMENT'] = $arFields['TYPE_STATEMENT'];
        }
        $iStatementId = $provider->Add([
            'IBLOCK_ID' => $this->getIblock(),
            'NAME' => $arFields['NAME'] ?? "Ярлык зпв",
            'ACTIVE' => $arFields['ACTIVE'] ?? 'Y',
            'CREATED_BY' => $arFields['CREATED_BY'] ?? null,
            'PROPERTY_VALUES' => $arProps
        ]);
        return $iStatementId;
    }

}