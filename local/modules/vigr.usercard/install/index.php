<?php
use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\ModuleManager,
    \Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Entity\Base,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\EventManager;



class vigr_usercard extends CModule{
    /** @var string */
    public $MODULE_ID = 'vigr.usercard';

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME = 'Вигрь:Карточка сотрудника';

    public function __construct(){
        include_once(__DIR__."/version.php");
    }
    public function DoInstall(){
        $this->InstallEvents();
        ModuleManager::registerModule($this->MODULE_ID);
    }
    public function DoUnInstall(){
        ModuleManager::unregisterModule($this->MODULE_ID);
        $this->UnInstallEvents();
    }
    public function InstallEvents(){
        EventManager::getInstance()->registerEventHandler(
            'main',
            "OnUserTypeBuildList",
            $this->MODULE_ID,
            "Vigr\UserCard\ksBeUserType",
            "getUserTypeDescription"
        );
        EventManager::getInstance()->registerEventHandler(
            'main',
            "OnUserTypeBuildList",
            $this->MODULE_ID,
            "Vigr\UserCard\ksLegalEntitiesUserType",
            "getUserTypeDescription"
        );
    }
    public function UnInstallEvents(){
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            "OnUserTypeBuildList",
            $this->MODULE_ID,
            "Vigr\UserCard\ksBeUserType",
            "getUserTypeDescription"
        );
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            "OnUserTypeBuildList",
            $this->MODULE_ID,
            "Vigr\UserCard\ksLegalEntitiesUserType",
            "getUserTypeDescription"
        );

    }
}