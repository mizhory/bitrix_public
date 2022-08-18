<?php

namespace Immo\Statements\Traits;

use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\CurrentUser;
use CMain;

trait ModuleTrait
{
    /**
     * Возвращает инстанс текущего пользователя
     *
     * @return CurrentUser
     */
    public static function getCurrentUser(): CurrentUser
    {
        return CurrentUser::get();
    }

    /**
     * Возвращает $APPLICATION
     *
     * @return CMain
     */
    public static function getApplication(): CMain
    {
        return new CMain();
    }

    /**
     * Возвращает путь модуля
     * @param false $absolute
     *
     * @return array|string|string[]
     */
    public static function getModulePath($absolute = false)
    {
        $path = dirname(__DIR__, 2);
        return $absolute ? str_ireplace(Application::getDocumentRoot(), '', $path) : $path;
    }

    /**
     * Обёртка над функцией in_array для рекурсивного поиска
     * @param $needle
     * @param $haystack
     * @param false $strict
     * @return bool
     */
    public static function inArrayRecursive($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {

            if(
                ($strict ? $item === $needle : $item == $needle) ||
                (is_array($item) && self::inArrayRecursive($needle, $item, $strict))
            ) {
                return true;
            }

        }

        return false;
    }

    public static function getContainer(): ServiceLocator
    {
        return ServiceLocator::getInstance();
    }
}