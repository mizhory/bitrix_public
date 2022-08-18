<?php

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\ModuleManager,
    \Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Entity\Base,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\EventManagerl;


class vigr_budget extends CModule
{
    /** @var string */
    public $MODULE_ID = 'vigr.budget';

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME = 'Вигрь:Бюджет';

    public function __construct()
    {
        include_once(__DIR__ . "/version.php");
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installEvents();
    }

    public function DoUnInstall()
    {
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        $this->unInstallEvents();
    }

    function installEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Vigr\Budget\Integration\Iblock\SortFields',
            'getUserTypeDescription'
        );

        $eventManager->registerEventHandlerCompatible(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\Budget\UserType\UserTypeSortFields',
            'getIBlockPropertyDescription'
        );
    }

    function unInstallEvents(){
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Vigr\Budget\Integration\Iblock\SortFields',
            'getUserTypeDescription'
        );

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\Budget\UserType\UserTypeSortFields',
            'getIBlockPropertyDescription'
        );
    }


}