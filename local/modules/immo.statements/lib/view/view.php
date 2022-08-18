<?php

namespace Immo\Statements\View;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CMain;
use Immo\Statements\Access\User;
use Immo\Statements\Data\Iblock;
use Immo\Statements\Data\SalaryStatement;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\IblockTrait;
use Throwable;

/**
 * Класс для рендеринга компонента ЗПВ
 */
class View implements ModuleInterface
{
    use IblockTrait;

    private array $viewModeByPage = [
        'company' => 'UR',
        'be' => 'BE'
    ];

    private array $arParams;
    private array $arResult;

    private HttpRequest $request;
    private CMain $application;
    private User $user;

    /**
     * @param HttpRequest $request
     * @return View
     */
    public function setRequest(HttpRequest $request): View
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return array
     */
    public function getArParams(): array
    {
        return $this->arParams;
    }

    /**
     * @param array $arParams
     * @return View
     */
    public function setArParams(array $arParams): View
    {
        $this->arParams = $arParams;
        return $this;
    }

    /**
     * @return array
     */
    public function getArResult(): array
    {
        return $this->arResult;
    }

    /**
     * @param array $arResult
     * @return View
     */
    public function setArResult(array $arResult): View
    {
        $this->arResult = $arResult;
        return $this;
    }

    /**
     * @param CMain $application
     * @return View
     */
    public function setApplication(CMain $application): View
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @param User $user
     * @return View
     */
    public function setUser(User $user): View
    {
        $this->user = $user;
        return $this;
    }

    public function __construct(&$arParams, &$arResult, User $user = null)
    {
        $this
            ->setArParams($arParams)
            ->setArResult($arResult)
            ->setApplication(new CMain())
            ->setRequest(Context::getCurrent()->getRequest())
            ->setUser($user ?? new User());
    }

    /**
     * Рендерит компонент на странице
     */
    public function render(): void
    {
        try {

            $roles = $this->getUserRoles();
            $options = $this->prepareOptions();
            $options['role'] = $roles[0];

        } catch (Throwable $e) {

        }

        $this->prepareViewMode($options);

        $this->application->IncludeComponent(
            'immo:statements.list',
            '',
            $options
        );
    }

    /**
     * Рендерит отдельную ведомость на детальной странице
     */
    public function renderDetails(): void
    {
        $options = $this->prepareOptions();

        $this->prepareElementId($options);

        try {
            $statement = new SalaryStatement($options['element_id']);

            $this->prepareElementData($statement, $options);
            $this->prepareDefaultRole($options);

            $this->application->IncludeComponent('immo:statements.details', '', $options);
        } catch (Throwable $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * Добавляет id элемента ИБ ЗПВ в массив параметров компонента
     *
     * @param array $options
     * @return array
     */
    protected function prepareElementId(array &$options): array
    {
        if(!empty($options['VARIABLES']['ID'])) {
            $options['element_id'] = (int) $options['VARIABLES']['ID'];
        }

        return $options;
    }

    /**
     * Добавляет роль текущего пользователя в массив параметров компонента
     *
     * @throws ArgumentException
     * @throws AccessDeniedException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareDefaultRole(array &$options)
    {
        if(!empty($options['role'])) {
            return;
        }

        $roles = $this->getUserRoles();

        foreach ($roles as $role) {
            if(in_array($role, self::GLOBAL_USER_ROLES, true)) {
                $options['role'] = $role;
                return $options;
            }
        }

        if(!is_null($options['VARIABLES']['ROLE']))  {
            $options['VARIABLES']['ROLE'] = strtoupper($options['VARIABLES']['ROLE']);
            $options['role'] = strtoupper($options['VARIABLES']['ROLE']);
            return $options;
        }

        if(!is_null($this->request['status_card_value_id']) || !is_null($options['status_card_value_id'])) {
            $scValueId = $this->request['status_card_value_id'] ?? $options['status_card_value_id'];

            $scValues = Iblock::getPropertiesListValues(
                'STATUS_CARD',
                $options['iblock_id'],
                $scValueId,
                true
            );

            $scXmlId = $scValues[$scValueId]['xml_id'];

            if(
                (array_key_exists($scXmlId, User::$statesByRole) && in_array(User::$statesByRole[$scXmlId], $roles)) ||
                User::getCurrentUser()->isAdmin()
            ) {
                $options['role'] = User::$statesByRole[$scXmlId];
                return $options;
            }
        }

        throw new AccessDeniedException('Доступ запрещён');
    }

    /**
     * Формирует массив параметров для отрисовки компонента отдельной ведомости
     *
     * @return array
     */
    protected function prepareOptions(): array
    {
        return array_merge($this->arResult, $this->request->toArray());
    }

    /**
     * Добавляет в массив опций параметр, определяющий, что выводить пользователю - БЕ или юрлица
     *
     * @param array $options
     * @return array
     */
    protected function prepareViewMode(array &$options): array
    {
        if($this->arResult['PAGE'] !== 'list') {
            $options['VIEW_MODE'] = $this->viewModeByPage[$this->arResult['PAGE']];
        }

        return $options;
    }

    /**
     * Добавляет данные из элемента ИБ ЗПВ в массив параметров для отрисовки компонента отдельной ведомости
     * @param SalaryStatement $statement
     * @param $options
     * @return array
     */
    protected function prepareElementData(SalaryStatement $statement, &$options): array
    {
        $options = array_merge($statement->getElement(), $options);
        return $options;
    }

    /**
     * Проверяет необходимость выбора представления по ролям
     * @return bool
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function canViewRoleSelector(): bool
    {
        return count($this->getUserRoles()) > 1;
    }

    /**
     * Возвращает список ролей пользователя
     *
     * @return array
     *
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    protected function getUserRoles(): array
    {
        $roles = $this->user->getSalaryStatementsRoles();
        return $roles['roles'];
    }
}