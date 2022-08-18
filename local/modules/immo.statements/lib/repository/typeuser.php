<?php

namespace Immo\Statements\Repository;

use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\IblockTrait;

/**
 * Работа с типами пользователя
 */
class TypeUser implements ModuleInterface
{
    use IblockTrait;

    /**
     * Тип пользователя по умолчанию
     * @var string
     */
    private static string $sDefaultTypeCode = 'STANDARD';
    private string $sIblockCode;
    private string $sIblockTypeCode;
    private int $iIblockId;

    private static ?TypeUser $obTypeUser = null;

    public function __construct()
    {
        $this->sIblockCode = static::IBLOCK_CODE_USER_TYPE;
        $this->sIblockTypeCode = static::IBLOCK_TYPE_BITRIX_LISTS;
        $this->iIblockId = static::getIblockId($this->sIblockCode, $this->sIblockTypeCode);
    }

    /**
     * ID Типа пользователя по умолчанию
     * @return int
     */
    public static function getDefaultTypeId(): int
    {
        $arType = static::getTypeByCode(static::$sDefaultTypeCode);
        return (int)$arType['ID'];
    }

    /**
     * Получить тип пользователя по коду типа
     * @param string $sCode
     * @return array
     */
    public static function getTypeByCode(string $sCode): array
    {
        $arTypes = static::getTypes();
        foreach ($arTypes as $arType) {
            if ($arType['CODE'] === $sCode) {
                return $arType;
            }
        }
        return [];
    }

    /**
     * Список всех типов
     * @return array
     */
    private static function getTypes(): array
    {
        if (static::$obTypeUser === null) {
            static::$obTypeUser = new static();
        }
        return static::$obTypeUser->getAllTypes();
    }

    /**
     * Список всех типов
     * @return array|null
     */
    private function getAllTypes(): array
    {
        static $arTypes;
        if ($arTypes === null) {
            $res = \CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID' => $this->iIblockId,
                    'ACTIVE' => 'Y',
                ],
                false,
                false,
                [
                    'ID', 'NAME', 'PROPERTY_' . 'CODE'
                ]
            );
            $arTypes = [];
            while ($arType = $res->Fetch()) {
                $arTypes[] = [
                    'ID' => $arType['ID'],
                    'NAME' => $arType['NAME'],
                    'CODE' => $arType['PROPERTY_' . 'CODE' . '_VALUE'],
                ];
            }
        }
        return $arTypes;
    }
}