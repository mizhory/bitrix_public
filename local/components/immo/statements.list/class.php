<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Immo\Statements\Component\StatementsComponent;
use Immo\Statements\Data\Department;
use Immo\Statements\Data\Iblock;
use Immo\Statements\Grid\Filter;
use Immo\Statements\Grid\Grid;
use Immo\Statements\ModuleInterface;
use Immo\Statements\UserType\UserField;

Loader::includeModule('immo.statements');

class StatementsListComponent extends StatementsComponent
{
    private Filter $filter;
    private Grid $grid;

    public function prepareResult(): void
    {
        $this->setIblockId();
        $this->setViewMode();
        $this->setFilter();
        $this->setGrid();

        $this->prepareFilter();
        $this->prepareGrid();

        $this->makeToolbar();
    }

    /**
     * Устанавливает ID инфоблока ЗПВ
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function setIblockId()
    {
        if(!isset($this->arParams['IBLOCK_ID'])) {
            $this->arParams['IBLOCK_ID'] = Iblock::getIblockId(
                ModuleInterface::IBLOCK_CODE_LABELS_SALARY,
                ModuleInterface::IBLOCK_TYPE_BITRIX_PROCESSES
            );
        }
    }

    /**
     * Устанавливает параметр VIEW_MODE в случае его отсутствия
     */
    private function setViewMode(): void
    {
        if(!is_null($this->arParams['role'])) {
            $this->arParams['VIEW_MODE'] = $this->user->getListViewModeByRole($this->arParams['role']);
        } elseif (!is_null($this->request['view_mode'])) {
            $this->arParams['VIEW_MODE'] = $this->request['view_mode'];
        }

    }

    /**
     * Формирует массив параметров фильтра
     *
     */
    private function prepareFilter(): void
    {
        $this->arResult['FILTER'] = [
            'FILTER_ID' => $this->prepareGridId(),
            'GRID_ID' => $this->prepareGridId(),
            'FILTER' => $this->filter->getFilter(),
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL' => true,
            'ENABLE_FIELDS_SEARCH' => true
        ];
    }

    /**
     * Формирует грид
     */
    private function prepareGrid(): void
    {

        $filterOptions = new FilterOptions($this->prepareGridId());

        $filter = $this->filter->parseFilter($filterOptions);

        $this->arResult['GRID'] = [
            'GRID_ID' => $this->prepareGridId(),
            'HEADERS' => $this->grid->getColumns(),
            'ROWS' => $this->grid->getRows($filter),
            'SHOW_ROW_CHECKBOXES' => false,
        ];

    }

    /**
     * Формирует ID грида
     *
     * @return string
     */
    private function prepareGridId(): string
    {
        return sprintf('immo_statements_%d', $this->arParams['IBLOCK_ID']);
    }

    /**
     * Добавляет фильтр на страницу
     */
    private function makeToolbar(): void
    {
        Toolbar::addFilter($this->arResult['FILTER']);
        $this->makeGenerationBtn();

        if($this->arParams['use_select_role'] === 'Y') {
            $this->makeRoleSelector();
        }
    }

    /**
     * @description Создает и добавляет в тулбар кнопку генерации ведомостей
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function makeGenerationBtn(): void
    {
        /**
         * Определяем роль пользователя и вытаскиваем его привязку к юрлицам
         */
        try {
            $user = new \Immo\Statements\Access\User();
            $roles = $user->getSalaryStatementsRoles();
        } catch (AccessDeniedException $accessDeniedException) {
            return;
        }

        /**
         * Если пользователь не является бухгалтером юрлица, или не задан в ролях, выходим
         */
        if (empty($roles['structure']) or !in_array(ModuleInterface::ROLE_COMPANY_ACCOUNTANT, $roles['roles'])) {
            return;
        }

        $date = new Date();
        $nextDate = clone $date;
        $nextDate->setDate($date->format('Y'), ((int)$date->format('m') + 1), $date->format('d'));

        $arParams = [
            'text' => 'Сгенерировать ведомость',
            'menu' => []
        ];

        foreach ([$date, $nextDate] as $date) {
            $arParams['menu']['items'][] = [
                'text' => 'Сгенерировать за ' . FormatDate('f Y', $date),
                'onclick' => ['handler' => 'window.GenerationSalary.eventHandler'],
                'data' => [
                    'time' => $date->getTimestamp(),
                    'structure' => $roles['structure']
                ]
            ];
        }

        Toolbar::addButton(
            (new Button($arParams))->setColor(\Bitrix\UI\Buttons\Color::PRIMARY)
        );
    }

    /**
     * Инициализирует инстанс Immo\Statements\Grid\Filter
     */
    private function setFilter(): void
    {
        $this->filter = new Filter($this->arParams['IBLOCK_ID'], $this->arParams['VIEW_MODE']);
    }

    /**
     * Инициализирует инстанс Immo\Statements\Grid\Grid
     */
    private function setGrid(): void
    {
        $this->grid = new Grid($this->arParams['IBLOCK_ID'], $this->arParams['VIEW_MODE']);
    }

    /**
     * @description Проверка генерации зарплатной ведомости
     * @param string $time
     * @param array $structure
     * @return string[]
     * @throws ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function checkGenerationAction(string $time, array $structure = []): array
    {
        /**
         * Проверка на введенный месяц. Вкладку могут открыть ночью либо в конце месяца.
         */
        $curDate = new Date();

        $errorStr = sprintf(
            'Извините, при формировании зарплатной ведомости от %s произошел сбой. '
            . 'Обновите страницу и снова нажмите на кнопку ручной генерации. '
            . 'При повторе сбоя - обратитесь к администратору Корпоративного портала.',
            \Immo\Statements\Generation\Salary::getFormatDate($curDate, 'F')
        );
        try {
            $date = new Date(date(Date::getFormat(), $time));
            if ($date->format('d') != $curDate->format('d')) {
                throw new SystemException($errorStr);
            }
        } catch (\Throwable $throwable) {
            throw new SystemException($errorStr);
        }

        $generator = new \Immo\Statements\Generation\Salary($date);

        $result = [];

        foreach ($structure as $be => $companies) {
            if ((int)$be <= 0) {
                $result[$be]['error'] = 'Ошибка БЕ';
                continue;
            }

            foreach ($companies as $companyId) {
                $result[$be]['companies'][$companyId] = [
                    'errors' => [],
                    'status' => 'success',
                    'text' => ''
                ];

                $resultCheck = $generator->checkGenerationSingle($be, $companyId);
                if (!$resultCheck->isSuccess()) {
                    $result[$be]['companies'][$companyId]['errors'] = $resultCheck->getErrorMessages();
                } else {
                    $result[$be]['companies'][$companyId]['text'] = sprintf(
                        'Пожалуйста, подождите. В данный момент генерируется зарплатная ведомость по %s от %s',
                        $generator::getCompanyName($companyId),
                        $generator::getFormatDate($generator->getDate(), 'F'),
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @description Запуск генерации ведомости
     * @param string $time
     * @param array $structure
     * @throws ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function generateSalaryAction(string $time, array $structure = []): void
    {
        $date = new Date(date(Date::getFormat(), $time));
        $generator = new \Immo\Statements\Generation\Salary($date, \Immo\Tools\User::getCurrent()->getId());
        foreach ($structure as $be => $companies) {
            $generator->putInQueue((int)$be, $companies);
        }
    }

    /**
     * @return array[][]
     */
    public function configureActions(): array
    {
        return [
            'checkGeneration' => [
                'prefilters' => [
                    new Authentication(),
                    new HttpMethod(
                        [HttpMethod::METHOD_POST]
                    ),
                    new Csrf(),
                ],
            ],
            'generateSalary' => [
                'prefilters' => [
                    new Authentication(),
                    new HttpMethod(
                        [HttpMethod::METHOD_POST]
                    ),
                    new Csrf(),
                ],
            ],
            'reloadComponent' => [
                'prefilters' => [
                    new Authentication(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Csrf()
                ]
            ]
        ];
    }

    /**
     * Формирует пункты меню для кнопки выбора представления
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareSelectorItems(): void
    {

        foreach ($this->arParams['roles'] as $role) {

            $data = [
                'iblock_id' => $this->arParams['IBLOCK_ID'],
                'grid_id' => $this->prepareGridId(),
                'role' => $role,
                'clear_nav' => 'Y',
                'view_mode' => $this->user->getListViewModeByRole($role),
                'component' => $this->getName(),
                'mode' => 'class',
                'action' => 'reloadComponent'
            ];

            $item = [
                'text' => UserField::getLabel($role, Department::getUfEntityId()),
                'onclick' => ['handler' => 'window.GenerationSalary.selectViewByRole'],
                'data' => array_merge($data, $this->arParams, $this->request->toArray())
            ];

            $this->arResult['role_selector_items'][] = $item;
        }
    }

    /**
     * Добавляет кнопку выбора представления, если у пользователя указано несколько ролей
     */
    protected function makeRoleSelector()
    {
        $this->prepareSelectorItems();

        $selectButton = new Button([
            'text' => 'Выбрать представление',
            'menu' => [
                'items' => $this->arResult['role_selector_items']
            ]
        ]);

        Toolbar::addButton($selectButton);
    }

    /**
     * Перезагружает компонент
     * Нужно доделать будет
     * @return Component
     */
    public function reloadComponentAction(): Component
    {

        $this->reloadComponent();

        return new Component($this->getName(), '', $this->request->toArray());
    }

    protected function reloadComponent(): void
    {
        $this->onPrepareComponentParams($this->arParams);
        $this->prepareResult();
    }
}