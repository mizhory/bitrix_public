<?php


namespace Immo\Statements\Generation;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Immo\Statements\Data\HLBlock;
use Immo\Statements\ModuleInterface;
use Immo\Tools\User;

/**
 * @description Класс для генерации зарплатных ведомостей
 * Class Salary
 * @package Immo\Statements\Generation
 */
class Salary extends UsersCompanies implements ModuleInterface
{
    use Helper, Queue;

    /**
     * @description Название хайлодблока очереди ручной генерации
     */
    public const HL_NAME_QUEUE_SALARY = 'QueueSalaryGeneration';

    /**
     * @description Значение лимита по пошаговой загрузке пользователей
     */
    protected const LIMIT = 100;

    /**
     * @var array Массив массив существующих ведомостей на текущий месяц и год
     */
    protected array $existSalaries = [];

    /**
     * @var HLBlock Сущность хайлодблока
     */
    protected HLBlock $entity;

    /**
     * @var Date Дата генерации ведомости
     */
    protected Date $date;

    /**
     * @var int ID пользователя, который запускает генерацию
     */
    protected int $userId = 0;

    /**
     * @var array Массив прочих параметров
     */
    protected array $params = [
        'MONTH' => '', // ID значения года
        'LIMIT' => 0, // ограничение
    ];

    /**
     * @description Возвращает объект даты
     * @return Date
     */
    public function getDate(): Date
    {
        return $this->date;
    }

    /**
     * @description Заполняет объект даты
     * @param Date $date
     */
    public function setDate(Date $date): void
    {
        $this->date = $date;
    }

    /**
     * @description Возвращает массив параметров
     * @return array|int[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @description Задает массив параметров
     * @param array|int[] $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @description Возвращает ID пользователя генератора ведомости
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @description Задает ID пользователя генератора ведомости
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * Salary constructor.
     * @param Date|null $date
     * @param int $userId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct(\Bitrix\Main\Type\Date $date = null, int $userId = 0)
    {
        $this->setDate((empty($date)) ? new \Bitrix\Main\Type\Date() : $date);
        $this->setUserId(($userId <= 0) ? User::getCurrent()->getId() : $userId);

        $this->setParams([
            'YEAR' => $this->getDate()->format('Y'),
            'MONTH' => static::getMonthId($this->getDate()->format('m'))
        ]);
    }

    /**
     * @description Метод генерации всех ведомостей
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function generate(): void
    {
        foreach (static::getStructure() as $beId => $structure) {
            $this->generateByBe($beId, array_keys($structure['COMPANIES']));
        }
    }

    /**
     * @description Метод генерации ведомостей по конкретной БЕ
     * @param int $beId
     * @param array $companyIds
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function generateByBe(int $beId, array $companyIds): int
    {
        $params = $this->getParams();
        $companyIds = $this->filterByCompanies($params['YEAR'], $params['MONTH'], $beId, $companyIds);
        if (empty($companyIds)) {
            return 0;
        }

        $beData = $this->getBeData($beId, $companyIds);
        if (empty($beData['COMPANIES'])) {
            return 0;
        }

        foreach ($beData['COMPANIES'] as $company) {
            $arUsersCompany = $beData['USERS'][$company['OLD_ID']];
            if (empty($arUsersCompany)) {
                continue;
            }

            $id = $this->createLabel($beId, $company['ID'], $params['YEAR'], $params['MONTH']);
            if ($id <= 0) {
                return 0;
            }

            foreach ($arUsersCompany as $user) {
                $this->generateUserSheet($id, $user, $beData['BE'], $company,
                    $this->getDate()->format('Y'),
                    $this->getDate()->format('m')
                );
            }
        }

        return $id ?? 0;
    }

    /**
     * @description Метод генерации пользователя в ведомость
     * @param int $id
     * @param array $arUser
     * @param array $arBe
     * @param array $company
     * @param int $year
     * @param string $month
     * @throws \Bitrix\Main\SystemException
     */
    protected function generateUserSheet(int $id, array $arUser, array $arBe, array $company, int $year, string $month)
    {
        if (empty($arUser['DATA']['ID'])) {
            return;
        }

        $fields = $this->prepareFields($id, $arUser, $arBe, $company, $year, $month);
        if (empty($fields)) {
            return;
        }

        $entityClass = $this->getHlEntity()->getEntity();
        if (empty($entityClass)) {
            return;
        }

        $entityClass::add($fields);
    }

    /**
     * @description Метод подготовки полей для генерции в зарплатную ведомость.
     * Принимает данные пользователя из карточки сотрудника, ID ярлыка и прочие данные
     * @param int $id ID ярлыка ведомости
     * @param array $arUser Данные о пользователе
     * @param array $arBe Данные о БЕ
     * @param array $company Данные о Юрлице
     * @param int $year ID значения месяца
     * @param string $month Номер месяца с нулем
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function prepareFields(int $id, array $arUser, array $arBe, array $company, int $year, string $month): array
    {
        $entityId = $this->getHlEntity()->getUfEntityId();

        $hlMonth = static::getHlEnum($entityId, 'UF_MONTH', $month);
        return [
            'UF_LABELS_SALARY_ELEMENT_ID' => $id, // ID ярлыка
            'UF_BE' => $arBe['ID'], // ID БЕ
            'UF_COMPANY' => $company['ID'], //  ID Юрлица
            'UF_USER' => $arUser['DATA']['ID'], // ID пользователя
            'UF_MONTH' => $hlMonth['ID'] ?? 0, // ID значения списка месяца
            'UF_YEAR' => $year, // год
            'UF_OFFER_SUM' => round($arUser['DATA']['UF_CS_BE']['salary'], 2), // Из пользователя: Оклад по офферу
            'UF_ADD_SUM' => round($arUser['DATA']['UF_CS_BE']['additionalSalary'], 2), // Из пользователя: Дополнительный оклад
            'UF_OVERPAYMENTS' => round($arUser['DATA']['UF_CS_BE']['overSalary'], 2), // Из пользователя: Переплаты

            'UF_1C_SUM' => round($arUser['COMPANY_INFO']['UF_SALARY_FIX'], 2), // Из хайлода: Оклад по 1С

            'UF_CURRENCY' => $this->defineCurrencyByCountry((int)$arBe['UF_COUNTRY']) // Валюта по БЕ
        ];
    }

    /**
     * @description Возвращает хайлоад сущность ведомостей
     * @return HLBlock
     */
    protected function getHlEntity(): HLBlock
    {
        if (empty($this->entity)) {
            $this->entity = new HLBlock(static::HL_ENTITY_STATEMENTS_APPROVAL);
        }

        return $this->entity;
    }

    /**
     * @description Метод создания ярлыка ведомости
     *
     * @param int $beId ID БЕ
     * @param int $companyId ID юрлица
     * @param int $year Календарный год
     * @param int $monthId ID месяца
     * @param bool $active флаг активности
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function createLabel(int $beId, int $companyId, int $year, int $monthId, bool $active = true): int
    {
        $iblockId = static::getIblockId();
        if ($iblockId <= 0) {
            return 0;
        }

        $provider = new \CIBlockElement();
        $id = $provider->Add([
            'IBLOCK_ID' => $iblockId,
            'NAME' => "Ярлык по БЕ {$beId} юр. лицо {$companyId}",
            'ACTIVE' => ($active) ? 'Y' : 'N',
            'CREATED_BY' => $this->getUserId(),
            'PROPERTY_VALUES' => [
                'SELECTED_BE' => $beId,
                'SELECTED_UR' => $companyId,
                'F_YEAR' => $year,
                'F_MONTH' => $monthId,
            ]
        ]);

        return ($id === false or !empty($provider->LAST_ERROR)) ? 0 : (int)$id;
    }

    /**
     * @description Возвращает структурированные данные о:
     * BE - бе
     * COMPANIES - юрлицах внутри этого БЕ
     * USERS - список пользователей в этих юрлицах
     *
     * @param int $beId ID БЕ
     * @param array $companyIds Массив ID юрлиц. Если указать пустой, достанутся все юрлица из БЕ
     * @param int $page Номер страницы для пошаговости
     * @return array|array[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getBeData(int $beId, array $companyIds, int $page = 1): array
    {
        $structure = static::getStructure();
        if (empty($structure[$beId]['COMPANIES'])) {
            return [];
        }

        foreach ($companyIds as $companyId) {
            if (empty($structure[$beId]['COMPANIES'][$companyId])) {
                continue;
            }

            $filter['@UF_COMPANY'][] = $structure[$beId]['COMPANIES'][$companyId]['OLD_ID'];
        }
        if (empty($filter)) {
            return [];
        }

        $companyUsers = $this->loadRows($filter, $this->getLimit(), $page);
        if (empty($companyUsers['ROWS'])) {
            return ['BE' => ['COUNT' => $companyUsers['COUNT']]];
        }

        $arUsersBe = $this->collectUsersInfo($companyUsers['ROWS']);
        $arUsersBe[$beId]['BE']['COUNT_USERS'] = $companyUsers['COUNT'];

        return $arUsersBe[$beId] ?? [];
    }

    /**
     * @description Проверка генерации ведомости
     * Возвращает true - если ведомости уже сгенерированы или в процессе генерации
     * Возвращает false - если ведомости еще не сгенерированы
     *
     * @param int $beId
     * @param array $companyIds
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function checkGeneration(int $beId, array $companyIds = []): bool
    {
        $params = $this->getParams();
        $arCompanies = static::filterByCompanies($params['YEAR'], $params['MONTH'], $beId, $companyIds);

        $generationCompanies = (empty($arCompanies));
        $this->checkElementQueue(
            $params['YEAR'],
            $params['MONTH'],
            $beId,
            $generationCompanies ? $companyIds : $arCompanies
        );

        return $generationCompanies;
    }

    /**
     * @description Проверка генерации ведомости для одного юрлица
     * @param int $beId
     * @param int $companyId
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function checkGenerationSingle(int $beId, int $companyId): Result
    {
        $result = new Result();
        try {
            $res = $this->checkGeneration($beId, [$companyId]);
            if ($res) {
                $result->addError(new Error(sprintf(
                    'Зарплатная ведомость для %s от %s уже сформирована. <a href="/sheets/salary/">Зарплатная ведомость</a>',
                    $this->getFormatDate($this->getDate(), 'F'),
                    $this->getCompanyName($companyId),
                )));
            }
        } catch (SystemException $systemException) {
            if ($systemException->getCode() == 100) {
                $result->addError(new Error($systemException->getMessage()));
            } else {
                throw $systemException;
            }
        }
        return $result;
    }

    /**
     * @description Метод для фильтрации генерации ведомостей
     * Достает существующие ведомости по параметрам.
     *
     * @param int $year Календарный год
     * @param int $month ID значения месяца
     * @param int $beId ID БЕ
     * @param array $companyIds массив ID юрлиц
     *
     * @return array Возвращает массив юрлиц, для которых еще не сгенерирова ведомость
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function filterByCompanies(int $year, int $month, int $beId, array $companyIds = []): array
    {
        if (empty($companyIds)) {
            $structure = static::getStructure();
            if (empty($structure) or empty($structure[$beId]['COMPANIES'])) {
                return [];
            }

            $companyIds = array_keys($structure[$beId]['COMPANIES']) ?? [];
        }

        $salaries = $this->getExistSalaries($year, $month);
        if (empty($salaries[$beId])) {
            return $companyIds;
        }

        return array_diff($companyIds, array_column($salaries[$beId], 'COMPANY'));
    }

    /**
     * @description Возвращает массив сущестсующих ведомостей на указанный месяц и год
     * @param int $year
     * @param string $month
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getExistSalaries(int $year, string $month): array
    {
        if (!empty($this->existSalaries)) {
            return $this->existSalaries;
        }

        $iblockId = static::getIblockId();
        if ($iblockId <= 0) {
            return [];
        }

        $query = \Immo\Iblock\Manager::getQueryElements($iblockId);
        if (empty($query)) {
            return [];
        }

        $rsSalary = $query
            ->where([
                ['F_MONTH.VALUE', $month],
                ['F_YEAR.VALUE', $year]
            ])
            ->whereNotNull('BE_ID')
            ->whereNotNull('COMPANY_ID')
            ->setSelect(['ID', 'BE_ID' => 'SELECTED_BE.VALUE', 'COMPANY_ID' => 'SELECTED_UR.VALUE', 'ACTIVE'])
            ->exec();
        while ($salary = $rsSalary->fetch()) {
            $beId = (int)$salary['BE_ID'];
            if (empty($this->existSalaries[$beId])) {
                $this->existSalaries[$beId] = [];
            }

            $this->existSalaries[$beId][] = [
                'COMPANY' => (int)$salary['COMPANY_ID'],
                'ID' => $salary['ID'],
                'ACTIVE' => $salary['ACTIVE']
            ];
        }

        return $this->existSalaries ?? [];
    }
}