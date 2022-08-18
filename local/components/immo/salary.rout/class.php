<?

namespace Immo\Component;

use Bitrix\Main\SystemException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @description Компонент рутинга зарплатных ведомостей
 * Class SalaryRout
 * @package Immo\Component
 */
class SalaryRout extends Router
{
    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        return $arParams;
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            $this->arResult = $this->definePage($this->arParams);
            if (empty($this->arResult['PAGE'])) {
                throw new SystemException('Ошибка адреса. Запрашиваемая страница не найдена');
            }

            $this->IncludeComponentTemplate($this->arResult['PAGE']);
        } catch (\Exception $error) {
            ShowError($error->getMessage());
        }
    }
}