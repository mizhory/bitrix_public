<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main;
use Immo\Iblock;
use Immo\Tools\User;

/**
 * @description Компонент для поля с поиском через dadata
 * Class IblockFinancialElement
 * @package Immo\Components
 */
class IblockPaymentRecipient extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    /**
     * @inheritDoc
     */
    public function configureActions()
    {
        return [
            'loadUsers' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    /**
     * @description Поиск пользователей по имени
     * @param string $value
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function loadUsersAction(string $value = ''): array
    {
        $result = [
            'items' => [],
            'value' => [
                'id' => $value,
                'text' => "{$value}"
            ]
        ];

        $users = User::findUsersName($value);
        if (empty($users)) {
            return $result;
        }

        foreach ($users as $userName) {
            $result['items'][] = [
                'id' => $userName,
                'text' => $userName,
            ];
        }

        return $result ?? [];
    }

    /**
     * @return mixed|void|null
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function executeComponent()
    {
        $this->arResult = $this->arParams;
        $this->arResult['TOKEN'] = $this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['TOKEN'];
        $this->preparePropsSelectors($this->arResult['PROPERTY']);
        $this->includeComponentTemplate();
    }

    /**
     * @description Собирает конфиг для привязка к другим свойствам
     * @param array $arProperty
     * @throws Main\ArgumentException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    protected function preparePropsSelectors(array $arProperty): void
    {
        if (empty($arProperty['IBLOCK_ID'])) {
            return;
        }

        $arProps = array_values($arProperty['USER_TYPE_SETTINGS']['PROPERTIES']);

        $arProperties = Iblock\Manager::loadProperties(
            Main\ORM\Query\Query::filter()
                ->where('IBLOCK_ID', $arProperty['IBLOCK_ID'])
                ->whereIn('CODE', $arProps),
            ['ID', 'CODE', 'PROPERTY_TYPE']
        );
        if (empty($arProperties)) {
            return;
        }

        $arFlipParams = array_flip($arProperty['USER_TYPE_SETTINGS']['PROPERTIES']);
        foreach ($arProperties as $property) {
            if (!array_key_exists($property['CODE'], $arFlipParams)) {
                continue;
            }

            switch ($property['PROPERTY_TYPE']) {
                case PropertyTable::TYPE_LIST:
                    $this->arResult['CONFIG'][$arFlipParams[$property['CODE']]] = [
                        'select' => "select[name^=PROPERTY_{$property['ID']}]",
                        'values' => Iblock\Manager::getEnums($property['ID'])
                    ];
                    break;

                case PropertyTable::TYPE_STRING:
                default:
                    $this->arResult['CONFIG'][$arFlipParams[$property['CODE']]] = [
                        'select' => "input[name^=PROPERTY_{$property['ID']}]"
                    ];
                    break;
            }
        }
    }
}