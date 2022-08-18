<?php
use \Bitrix\Main\ModuleManager,
    \Bitrix\Main\Entity\Base,
    \Bitrix\Main\EventManagerl;


class vigr_customfield extends CModule
{
    /** @var string */
    public $MODULE_ID = 'vigr.customfield';

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME = 'Вигрь:Пользовательские Свойства';

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
            '\Vigr\CustomField\UserType\userTypeVacationDays',
            'getUserTypeDescription'
        );

        $eventManager->registerEventHandlerCompatible(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeVacationDays',
            'getIBlockPropertyDescription'
        );

        $eventManager->registerEventHandlerCompatible(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeUlAndHeads',
            'getUserTypeDescription'
        );

        $eventManager->registerEventHandlerCompatible(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeUlAndHeads',
            'getIBlockPropertyDescription'
        );

        $eventManager->registerEventHandlerCompatible(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeHeadAndSuccessors',
            'getUserTypeDescription'
        );

        $eventManager->registerEventHandlerCompatible(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeHeadAndSuccessors',
            'getIBlockPropertyDescription'
        );
    }

    function unInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeVacationDays',
            'getUserTypeDescription'
        );

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeVacationDays',
            'getIBlockPropertyDescription'
        );

        $eventManager->unRegisterEventHandler(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeUlAndHeads',
            'getUserTypeDescription'
        );

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeUlAndHeads',
            'getIBlockPropertyDescription'
        );

        $eventManager->unRegisterEventHandler(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeHeadAndSuccessors',
            'getUserTypeDescription'
        );

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Vigr\CustomField\UserType\userTypeHeadAndSuccessors',
            'getIBlockPropertyDescription'
        );
    }


}