<?php
use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\ModuleManager,
    \Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Entity\Base,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\EventManager;



class vigr_customfields extends CModule{
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

    }
    public function UnInstallEvents(){

    }
}