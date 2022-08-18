<?php

namespace Immo\Statements\Calculator;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Web\Json;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\ModuleTrait;

abstract class StatementsCalculator implements ModuleInterface, StatementsCalculatorInterface
{
    use ModuleTrait;

    /**
     * Глобальная настройка "Базовый НДФЛ для расчетов, %"
     * @var string
     */
    public const GLOBAL_SETTINGS_PERSONAL_INCOME_TAX_BASED = 'UF_PERSONAL_INCOME_TAX_BASED';

    /**
     * Глобальная настройка "Повышенный НДФЛ для расчетов, %"
     * @var string
     */
    public const GLOBAL_SETTINGS_PERSONAL_INCOME_TAX_INCREASED = 'UF_PERSONAL_INCOME_TAX_INCREASED';

    /**
     *
     * Глобальная настройка "Граница повышения НДФЛ"
     * @var string
     */
    public const GLOBAL_SETTINGS_TAX_BORDER_SUM = 'UF_TAX_BORDER_SUM';

    protected array $element;

    public function __construct(array $element)
    {
        $this->element = $element;
    }

    /**
     * @inheritDoc
     */
    public function getElement(): array
    {
        return $this->element;
    }

    /**
     * Проверяет сущность HL-блока в обработчике событий
     *
     * @param Entity $entity
     * @param string $hlName
     * @return bool
     */
    public static function checkHlEntityInEventHandler(Entity $entity, string $hlName): bool
    {
        return $entity->getName() === $hlName;
    }

    /**
     * Формирует вид значения полей типа "деньги"
     * @param $value
     * @return float
     */
    public function prepareValueFormat($value): float
    {
        return (float) number_format($value, 2, ',', ' ');
    }

    /**
     * Вытаскивает число из значения поля "Деньги"
     *
     * @param $field
     * @return float
     */
    public function prepareMoneyValueBeforeCalculation($field): float
    {
        $value = explode('|', $this->element[$field]);
        return (float) $value[0];
    }

    /**
     * Возвращает значение "Переплата" из карточки сотрудника для вставки в расчётное поле
     *
     * @return mixed
     *
     * @throws ArgumentException
     */
    public function getOverpaymentSum()
    {
        $csBe = $this->getCsBe();
        return $csBe['overSalary'];
    }

    /**
     * Получение параметра НДФЛ для дальнейшего использования в расчётных полях
     * @param string $ufTaxSettings
     * @return float
     */
    public function getPercentValue(string $ufTaxSettings): float
    {
        $tax = Option::get('askaron.settings', $ufTaxSettings);
        return $tax === 13 ? 0.87 : 0.85;
    }

    /** Возвращает значение поля "КС БЕ"
     *
     * @return array|null
     * @throws ArgumentException
     */
    public function getCsBe(): ?array
    {
        return !is_null($this->element['UF_CS_BE']) ? Json::decode($this->element['UF_CS_BE']) : null;
    }

    /**
     * Возвращает значение "Остаток до 15%" из карточки сотрудника для вставки в расчётное поле
     *
     * @return mixed
     * @throws ArgumentException
     */
    public function getBalance()
    {
        $csBe = $this->getCsBe();
        return $csBe['balance'];
    }

    /**
     * Граница повышения НДФЛ
     * @return int
     */
    public function getTaxBorder(): int
    {
        return (int) Option::get('askaron.settings', self::GLOBAL_SETTINGS_TAX_BORDER_SUM);
    }
}