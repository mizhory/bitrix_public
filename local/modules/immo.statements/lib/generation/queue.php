<?php


namespace Immo\Statements\Generation;


use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
use Immo\Iblock\Manager;
use Immo\Statements\Data\HLBlock;
use Immo\Tools\DatabaseWork;

/**
 * @description Трейт для работы с очередями и пошаговой генерацией на кроне
 * Trait Queue
 * @package Immo\Statements\Generation
 */
trait Queue
{
    use Agent;

    /**
     * @description Установка лимита выборки пользователей при генерации
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->params['LIMIT'] = $limit;
    }

    /**
     * @description Возвращает лимит выборки пользователей
     * @return int
     */
    public function getLimit(): int
    {
        return (int)$this->params['LIMIT'];
    }

    /**
     * @description Возвращает класс сущности хайлоадблока для работы с очередями
     * @return DataManager|class-string
     * @throws \Bitrix\Main\SystemException
     */
    protected function getQueueEntityClass(): string
    {
        return (new HLBlock(static::HL_NAME_QUEUE_SALARY))->getEntity();
    }

    /**
     * @description Создает элементы в очереди.
     * Первым делом создает сам ярлык ведомости, затем создает запись в хайлоадблоке
     * @param int $beId
     * @param array $companies
     * @throws SystemException
     */
    public function putInQueue(int $beId, array $companies = []): void
    {
        if (empty($companies)) {
            $structure = static::getStructure();
            if (empty($structure) or empty($structure[$beId]['COMPANIES'])) {
                return;
            }

            $companies = array_keys($structure[$beId]['COMPANIES']) ?? [];
        }

        if (empty($companies)) {
            return;
        }

        $params = $this->getParams();

        foreach ($companies as $id) {
            $labelId = $this->createLabel($beId, $id, $params['YEAR'], $params['MONTH'], false);
            if ($labelId <= 0) {
                continue;
            }

            $this->createElementQueue($labelId);
        }
    }

    /**
     * @description Метод для пошаговой генерации с транзакцией в БД
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function workWithQueue(): void
    {
        DatabaseWork::safe(Application::getConnection(), [$this, 'iterateQueue'], []);
    }

    /**
     * @description Итерация по очереди и ее обработка
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function iterateQueue(): void
    {
        foreach ($this->getSheetsQueue() as $beId => $arLabel) {
            $this->processLabel($arLabel);
        }
    }

    /**
     * @description Обработка ведомости в очереди
     * @param array $label
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    protected function processLabel(array $label): void
    {
        /**
         * Достаем данные по ведомости и ID в очереди
         */
        $arData = $this->getBeData($label['BE_ID'], [$label['COMPANY_ID']], (int)$label['QUEUE_REF']['PAGE']);
        $elementQueueId = (int)$label['QUEUE_REF']['ID'];
        if ($elementQueueId <= 0) {
            return;
        }

        /**
         * Считаем смещение для запроса
         */
        $offset = ((int)$label['QUEUE_REF']['PAGE'] - 1) * $this->getLimit();

        /**
         * Так как обрабортка идет по одной ведомости, то юрлицо здесь одно
         */
        $company = current($arData['COMPANIES']);

        /**
         * Если юрлицо не найдено или значение смещения больше чем кол-во пользователей в юрлице
         * Значит генерация ведомости закончилась и нужно убрать элемент в очереди
         */
        if (empty($company) or $offset > $arData['BE']['COUNT_USERS']) {
            $this->removeFromQueue($label, $elementQueueId);
            return;
        }

        $arUsersCompany = $arData['USERS'][$company['OLD_ID']];
        if (empty($arUsersCompany)) {
            return;
        }

        /**
         * Итерируемся по пользователям в юрлице и добавляем их в ярлык ведомости
         */
        foreach ($arUsersCompany as $user) {
            if (array_key_exists($user['DATA']['ID'], $label['USERS'])) {
                continue;
            }

            $this->generateUserSheet(
                $label['ID'],
                $user,
                $arData['BE'],
                $company,
                $label['YEAR_VALUE'],
                $label['MONTH_NUM']
            );
        }

        /**
         * Если значение смещения больше чем кол-во пользователей в юрлице
         * Значит генерация ведомости закончилась и нужно убрать элемент в очереди
         *
         * Иначе нужно увеличить значение страницы для обработки.
         * На следующей итерации крона будет запущена следующая страница
         */
        if ($offset >= $arData['BE']['COUNT_USERS'] or count($arUsersCompany) >= $arData['BE']['COUNT_USERS']) {
            $this->removeFromQueue($label, $elementQueueId);
        } else {
            $this->increasePageElementQueue($elementQueueId, ++$label['QUEUE_REF']['PAGE']);
        }
    }

    /**
     * @description Возвращает массив ведомостей, которые находятся в очереди и которые нужно обработать
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getSheetsQueue(): array
    {
        $iblockId = static::getIblockId();
        if ($iblockId <= 0) {
            return [];
        }

        $query = Manager::getQueryElements($iblockId);
        if (empty($query)) {
            return [];
        }

        $elements = $this->getElementsQueue();
        if (empty($elements)) {
            return [];
        }

        $rsSheets = $query
            ->whereIn('ID', array_keys($elements))
            ->whereNotNull('SELECTED_BE.VALUE')
            ->whereNotNull('SELECTED_UR.VALUE')
            ->whereNotNull('F_MONTH.VALUE')
            ->whereNotNull('F_YEAR.VALUE')
            ->whereNot('ACTIVE')
            ->setSelect([
                'ID',
                'BE_ID' => 'SELECTED_BE.VALUE',
                'COMPANY_ID' => 'SELECTED_UR.VALUE',
                'MONTH' => 'F_MONTH.VALUE',
                'MONTH_NUM' => 'MONTH_ENUM.XML_ID',
                'MONTH_VALUE' => 'MONTH_ENUM.VALUE',
                'YEAR_VALUE' => 'F_YEAR.VALUE',
                'CREATED_BY',
                'ACTIVE'
            ])
            ->registerRuntimeField(new ReferenceField(
                'MONTH_ENUM',
                PropertyEnumerationTable::class,
                Join::on('this.MONTH', 'ref.ID')
            ))
            ->exec();

        while ($sheet = $rsSheets->fetch()) {
            $sheet['COMPANY_ID'] = (int)$sheet['COMPANY_ID'];
            $sheet['BE_ID'] = (int)$sheet['BE_ID'];
            $sheet['MONTH'] = (int)$sheet['MONTH'];
            $sheet['YEAR'] = (int)$sheet['YEAR_VALUE'];
            $sheet['QUEUE_REF'] = $elements[$sheet['ID']];
            $sheet['DATE'] = $elements[$sheet['ID']];
            $sheet['USERS'] = [];
            $arSheets[$sheet['ID']] = $sheet;
        }

        if (!empty($arSheets)) {
            $this->fillSheetsUsers($arSheets);
        }

        return $arSheets ?? [];
    }

    /**
     * @description Заполняет ярлыки ведомостями пользователями.
     * Нужно для того, чтобы случайно не сгенерировать ведомость повторно на одного и того же пользователя
     * @param array $arSheets
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function fillSheetsUsers(array &$arSheets): void
    {
        $arIds = [];
        foreach ($arSheets as $beId => $arSheet) {
            $arIds = array_merge($arIds, array_column($arSheet, 'ID'));
        }

        if (empty($arIds)) {
            return;
        }

        $classEntity = $entity = $this->getHlEntity()->getEntity();
        if (empty($classEntity)) {
            return;
        }

        $rsSheet = $classEntity::query()
            ->whereIn('UF_LABELS_SALARY_ELEMENT_ID', $arIds)
            ->setSelect([
                'ID',
                'LABEL_ID' => 'UF_LABELS_SALARY_ELEMENT_ID',
                'UF_USER'
            ])
            ->exec();
        while ($userSheet = $rsSheet->fetch()) {
            $arUserSheets[$userSheet['LABEL_ID']][$userSheet['UF_USER']] = $userSheet;
        }

        if (!empty($arUserSheets)) {
            foreach ($arSheets as $beId => $arLabel) {
                foreach ($arLabel as $companyId => $label) {
                    if (empty($arUserSheets[$label['ID']])) {
                        continue;
                    }

                    $arSheets[$beId][$companyId]['USERS'] = $arUserSheets[$label['ID']];
                }
            }
        }
    }

    /**
     * @description Возвращает активные элементы очереди
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getElementsQueue(): array
    {
        $entityClass = $this->getQueueEntityClass();
        if (empty($entityClass)) {
            return [];
        }

        $rsQueue =$entityClass::query()
            ->where('UF_RUNNING')
            ->whereNot('UF_SHEET')
            ->setSelect([
                'ID',
                'UF_SHEET',
                'PAGE' => 'UF_PAGE',
                'UF_RUNNING'
            ])
            ->exec();
        while ($element = $rsQueue->fetch()) {
            $element['UF_RUNNING'] = (bool)$element['UF_RUNNING'];
            $arQueueElements[$element['UF_SHEET']] = $element;
        }

        return $arQueueElements ?? [];
    }

    /**
     * @description Создает элемент очереди
     * @param int $labelId
     * @throws SystemException
     */
    protected function createElementQueue(int $labelId)
    {
        $entityClass = $this->getQueueEntityClass();
        if (empty($entityClass)) {
            return;
        }

        $entityClass::add([
            'UF_USER' => $this->getUserId(),
            'UF_DATE' => $this->getDate(),
            'UF_SHEET' => $labelId,
            'UF_RUNNING' => 'Y',
            'PAGE' => 1
        ]);
    }

    /**
     * @description Обновляет значение страницы элемента очереди, для конкретной ведомости
     * @param int $queueId
     * @param int $page
     * @throws SystemException
     */
    protected function increasePageElementQueue(int $queueId, int $page)
    {
        $entityClass = $this->getQueueEntityClass();
        if (empty($entityClass)) {
            return;
        }

        $entityClass::update($queueId, ['UF_PAGE' => $page]);
    }

    /**
     * @description Удаляет значение из очереди
     *
     * @param array $label
     * @param int $elementQueueId
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function removeFromQueue(array $label, int $elementQueueId): void
    {
        $entityClass = $this->getQueueEntityClass();
        if (empty($entityClass)) {
            return;
        }

        /**
         * Снимаем флаг генерации с элемента очереди
         */
        $entityClass::update($elementQueueId, ['UF_RUNNING' => false]);

        /**
         * Актвируем ярлык ведомости
         */
        $provider = new \CIBlockElement();
        $provider->Update($label['ID'], ['ACTIVE' => 'Y']);

        /**
         * Отправляем уведомление пользователю
         */
        if (\Bitrix\Main\Loader::includeModule('im') and !empty($userId = $label['CREATED_BY'])) {
            \CIMNotify::Add([
                "NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
                "NOTIFY_MESSAGE" =>
                    sprintf(
                        'Ведомость для %s от %s уже сформирована и отправлена на согласование. Подробнее <a href="/sheets/salary/">Зарплатная ведомость</a>',
                        static::getCompanyName($label['COMPANY_ID']),
                        "{$label['MONTH_VALUE']} {$label['YEAR_VALUE']}",
                    ),
                "NOTIFY_MODULE" => "immo.statements",
                'TO_USER_ID' => $userId
            ]);
        }
    }

    /**
     * @description Проверяет статус ведомостей, по которым запустилась генерация, но еще не закончена
     * @param int $year
     * @param int $month
     * @param int $beId
     * @param array $companies
     * @return bool
     * @throws SystemException
     */
    public function checkElementQueue(int $year, int $month, int $beId, array $companies = []): bool
    {
        if (empty($companies)) {
            $structure = static::getStructure();
            if (!empty($structure[$beId]['COMPANIES'])) {
                $companies = array_keys($structure[$beId]['COMPANIES']) ?? [];
            }
        }

        /**
         * Достаем ведомости по параметрам
         * Если по запрашиваемым юрлицам нет ведомостей, то возвращает false - чтобы они сгенерировались
         * Иначе ведомости есть и их надо проверить на генерацию
         */
        $salaries = $this->getExistSalaries($year, $month);
        if (empty($salaries[$beId])) {
            return false;
        }

        /**
         * Итерируемся по ярлыкам ведомости.
         * Первым делом проверяем вхождение указанных юрлиц к генерации
         */
        foreach ($salaries[$beId] as $label) {
            if (!in_array($label['COMPANY'], $companies)) {
                continue;
            }

            if ($label['ACTIVE'] == 'Y') {
                continue;
            }

            $arErrors[] = sprintf(
                'Пожалуйста, подождите. В данный момент генерируется зарплатная ведомость по %s от %s. <a href="/sheets/salary/">Список зарплатная ведомость</a>',
                static::getCompanyName((int)$label['COMPANY']),
                static::getFormatDate($this->getDate(), 'F')
            );
        }

        if (!empty($arErrors)) {
            throw new SystemException(implode('<br>', $arErrors), 100);
        }

        return false;
    }
}