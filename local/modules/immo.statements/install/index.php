<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\EventManager;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;
use Immo\Statements\Entity\GridCalculatedFieldsTable;
use Immo\Statements\Entity\GridFieldsTable;
use Immo\Statements\UserType\PropertyCurrencyList;
use Immo\Statements\UserType\UserTypeCurrencyList;

Loc::loadMessages(__FILE__);

/**
 * @property Connection $connection
 * @property EventManager $eventManager
 * @property HttpRequest $request
 */
class immo_statements extends CModule
{
    public $MODULE_ID = 'immo.statements';

    private $events = [
        [
            'from_module_id' => 'main',
            'event' => 'OnUserTypeBuildList',
            'class' => UserTypeCurrencyList::class,
            'method' => 'getUserTypeDescription'
        ],
        [
            'from_module_id' => 'iblock',
            'event' => 'OnIBlockPropertyBuildList',
            'class' => PropertyCurrencyList::class,
            'method' => 'getUserTypeDescription'
        ],
    ];

    public function __construct()
    {
        $this->MODULE_NAME = Loc::getMessage("IS_MODULE_NAME");

        $this->connection = \Bitrix\Main\Application::getConnection();
        $this->request = Context::getCurrent()->getRequest();
        $this->eventManager = EventManager::getInstance();
    }

    /**
     * Установка модуля, добавление уровней доступа к нему в общую таблицу
     */
    public function DoInstall(): void
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallTasks();
        $this->InstallEvents();

        $this->showInstallStep('install');
    }

    /**
     * Удаление модуля и уровней доступа к нему
     */
    function DoUninstall(): void
    {
        $this->UnInstallEvents();
        $this->UnInstallTasks();

        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->showInstallStep('uninstall', 2);


    }

    /**
     * Список уровней доступа к модулю
     * @return array[]
     */
    public function GetModuleTasks(): array
    {
        return [
            'statements_deny' => [
                'LETTER' => 'D',
                'OPERATIONS' => []
            ],
            'statements_limited_read' => [
                'LETTER' => 'C',
                'OPERATIONS' => [
                    'read_current_state',
                ]
            ],
            'statements_read' => [
                'LETTER' => 'R',
                'OPERATIONS' => [
                    'read_current_state',
                    'read_all',
                ]
            ],
            'statements_limited_edit' => [
                'LETTER' => 'E',
                'OPERATIONS' => [
                    'read_current_state',
                    'edit_current_state'
                ]
            ],
            'statements_edit' => [
                'LETTER' => 'W',
                'OPERATIONS' => [
                    'read_current_state',
                    'edit_current_state',
                    'read_all',
                    'edit_all'
                ]
            ],
            'statements_manual_start' => [
                'LETTER' => 'M',
                'OPERATIONS' => [
                    'read_current_state',
                    'edit_current_state',
                    'manual_start'
                ]
            ],
            'statements_full' => [
                'LETTER' => 'X',
                'OPERATIONS' => [
                    'read_current_state',
                    'edit_current_state',
                    'read_all',
                    'edit_all',
                    'manual_start'
                ]
            ]
        ];
    }

    /**
     * Подключает пошаговые файлы в процессе установки/удаления модуля
     *
     * @param string $type
     * @param int $step
     */
    private function showInstallStep(string $type, int $step = 1)
    {
        global $APPLICATION;

        $fileName = sprintf('%sstep%d.php', ($type === 'uninstall' ? 'un' : ''), $step);
        $filePath = sprintf('%s/%s', __DIR__, $fileName);
        $langPhrase = sprintf('IMMO_STATEMENTS_MODULE_%s_STEP_TITLE', strtoupper($type));

        $APPLICATION->IncludeAdminFile(Loc::getMessage($langPhrase), $filePath);
    }

    public function InstallEvents()
    {

        foreach ($this->events as $event) {
            $this->eventManager->registerEventHandler(
                $event['from_module_id'],
                $event['event'],
                $this->MODULE_ID,
                $event['class'],
                $event['method']
            );
        }
    }

    public function UnInstallEvents()
    {
        foreach ($this->events as $event) {
            $this->eventManager->unRegisterEventHandler(
                $event['from_module_id'],
                $event['event'],
                $this->MODULE_ID,
                $event['class'],
                $event['method']
            );
        }
    }
}