<?php

namespace Immo\Statements\Repository;

use Bitrix\Main\UserGroupTable;
use Immo\Statements\Generation\UsersCompanies;

class EmployeeRepository extends UsersCompanies
{
    /**
     * НЕ нуждается в реализации, т.к этот класс используется для работы с данными пользователя.
     * @return void
     */
    public function generate(): void
    {

    }

    /**
     * Поиск ID  внештатных сотрудников
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFreelanceEmployeesUsersIds(array $arExistsUsersIds = []): array
    {
        // выбор пользователей из групп экстранет
        $resFreelance = UserGroupTable::getList(
            [
                'filter' => [
                    '=USER.ACTIVE' => 'Y',
                    '=GROUP.STRING_ID' => 'extranet_users'
                ],
                'select' => [
                    'USER_ID'
                ]
            ]
        );
        // массив ID внештатных сотрудников
        $arExtranetUsersIds = [];
        if (count($arExistsUsersIds)) {
            while ($arExtranetUsers = $resFreelance->fetch()) {
                $arExtranetUsersIds[] = $arExtranetUsers['USER_ID'];
            }
        } else {
            while ($arExtranetUsers = $resFreelance->fetch()) {
                if (!in_array($arExtranetUsers['USER_ID'], $arExistsUsersIds)) {
                    $arExtranetUsersIds[] = $arExtranetUsers['USER_ID'];
                }
            }
        }

        return $arExtranetUsersIds;
    }

    /**
     * Список сотрудников внештатных
     * @param array $arExistsUsersIds
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFreelanceEmployee(array $arExistsUsersIds = []): array
    {
        $obFreelanceEmployee = new static();
        $arUsersIds = static::getFreelanceEmployeesUsersIds($arExistsUsersIds);
        return $obFreelanceEmployee->loadUsersByIds($arUsersIds);
    }
}