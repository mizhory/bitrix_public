<?php

namespace Vigr\Budget\Integration\Iblock;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\UserField\Types\BaseType;


/**
 * Class dadataField
 * @package Vigr\Budget\Integration\Iblock
 * Поле дадаты в сделке - наименование - ИНН
 */
class dadataField extends BaseType{

    public const
        USER_TYPE_ID = 'propertydadata',
        MODE_VIEW = 'vigr:budget.dadata';

    protected const
        RENDER_COMPONENT = 'vigr:budget.dadata';

    private const
        MODE_ADMIN_SETTINGS = 'main.admin_settings',
        MODE_EDIT_FORM = 'main.edit_form',
        MODE_FILTER_HTML = 'main.filter_html',
        MODE_ADMIN_LIST_VIEW_HTML = 'main.admin_list_view_html',
        MODE_ADMIN_LIST_EDIT_HTML = 'main.admin_list_edit_html',
        MODE_PUBLIC_TEXT = 'main.public_text';

    public static function getUserTypeDescription() : array
    {
        return array_merge(
            [
                'PROPERTY_TYPE'=>'S',
                'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
                "USER_TYPE_ID" => self::USER_TYPE_ID,
                "DESCRIPTION" => 'Дадата',
                "GetPublicView" => array(__CLASS__,'getPublicView'),
                "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML'),
                "GetAdminViewHTML" => array(__CLASS__,'GetAdminListViewHTML'),
                'VIEW_CALLBACK' => [static::class, 'renderView'],
                'EDIT_CALLBACK' => [static::class, 'renderEdit'],
                'USE_FIELD_COMPONENT' => true
            ],
            parent::getUserTypeDescription()
        );

    }

    public static function renderAdminListView(array $userField, ?array $additionalParameters): string{
        return 'admin';
    }

    public static function getPublicText(array $userField): string
    {
        return  'rtex';
    }

    public static function renderView(array $userField, ?array $additionalParameters = []) : string{
        return 'Тут будет инфа! не трогать';
    }

    public static function getPublicView(array $userField, ?array $additionalParameters = []): string{
        return '123';
    }

    public static function GetAdminListViewHTML(array $userField, ?array $additionalParameters){
        return '123';
    }

    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => 'Дадата',
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING
        ];
    }

    /**
     * @return string
     */
    public static function getDbColumnType(): string
    {
        return 'text';
    }

}