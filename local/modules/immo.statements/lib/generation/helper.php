<?php


namespace Immo\Statements\Generation;


use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Type\Date;
use Immo\Iblock\Manager;
use Immo\Structure\Organization;

/**
 * @description Класс хелпера для генерации зарплатных ведомостей
 * Trait Helper
 * @package Immo\Statements\Generation
 */
trait Helper
{
    /**
     * @description Определение валюты по ID страны плательщика
     * @param int $countryId
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function defineCurrencyByCountry(int $countryId): int
    {
        if ($countryId <= 0) {
            return 0;
        }

        static $countryCurrencies = [];
        if ($countryCurrencies[$countryId] > 0) {
            return $countryCurrencies[$countryId];
        }

        $iblockId = Manager::getIblockId('country');
        if ($iblockId <= 0) {
            return 0;
        }

        $arCountry = \CIBlockElement::GetList([], [
            'ID' => $countryId,
            'IBLOCK_ID' => $iblockId
        ], false, false, [
            'IBLOCK_ID',
            'ID',
            'PROPERTY_VALYUTA'
        ])->Fetch();

        $countryCurrencies[$countryId] = (int)$arCountry['PROPERTY_VALYUTA_VALUE'];
        return $countryCurrencies[$countryId];
    }

    /**
     * @description Возвращает данные по значению поля список хайлодблока
     * @param string $entityId
     * @param string $fieldName
     * @param string $xmlId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getHlEnum(string $entityId, string $fieldName, string $xmlId): array
    {
        static $arEnums = [];

        if (!empty($arEnums[$entityId][$xmlId])) {
            return $arEnums[$entityId][$xmlId];
        }

        $arField = \Bitrix\Main\UserFieldTable::query()
            ->where([
                ['ENTITY_ID', $entityId],
                ['FIELD_NAME', $fieldName]
            ])
            ->addSelect('ID')
            ->exec()
            ->fetch();
        if (empty($arField)) {
            return [];
        }

        $rsFieldEnums = (new \CUserFieldEnum())->GetList([], ['USER_FIELD_ID' => $arField['ID']]);
        while ($enum = $rsFieldEnums->Fetch()) {
            $arEnums[$entityId][$enum['XML_ID']] = $enum;
        }

        return $arEnums[$entityId][$xmlId] ?? [];
    }

    /**
     * @description Возвращает ID инфоблока ярлыков зарплатных ведомостей
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIblockId(): int
    {
        return \Immo\Iblock\Manager::getIblockId(static::IBLOCK_CODE_LABELS_SALARY);
    }

    /**
     * @description Возвращает ID месяца из значений свойства инфоблока ярлыков зарплатных ведомостей
     * @param string $monthNum
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMonthId(string $monthNum): int
    {
        $iblockId = static::getIblockId();
        if ($iblockId <= 0) {
            return 0;
        }

        $monthProperty = \Immo\Iblock\Manager::getPropertyByCode('F_MONTH', $iblockId);
        if (empty($monthProperty)) {
            return 0;
        }

        $arEnum = \Immo\Iblock\Manager::getEnumByCode($monthProperty['ID'], $monthNum);
        return (int)$arEnum['ID'];
    }

    /**
     * @description Возвращает массив структуры
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getStructure(): array
    {
        static $structure = [];
        if (!empty($structure)) {
            return $structure;
        }

        $structure = Organization::getStructure();
        return $structure;
    }

    /**
     * @description Возвращает название БЕ
     * @param int $beId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getBeName(int $beId): string
    {
        $structure = static::getStructure();
        return (string)$structure[$beId]['BE']['NAME'];
    }

    /**
     * @description Возвращает название юрлица
     * @param int $companyId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getCompanyName(int $companyId): string
    {
        $structure = static::getStructure();
        foreach ($structure as $arBe) {
            if (empty($arBe['COMPANIES'][$companyId])) {
                continue;
            }

            $name = $arBe['COMPANIES'][$companyId]['NAME'];
            break;
        }

        return $name ?? '';
    }

    /**
     * @description Возврщает строку даты в формате "Февраль 2022"
     * @param Date $date
     * @param string $monthFormat
     * @return string
     */
    public static function getFormatDate(Date $date, string $monthFormat = 'f'): string
    {
        return FormatDate("{$monthFormat} Y", $date);
    }
}