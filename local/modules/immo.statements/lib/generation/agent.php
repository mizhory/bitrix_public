<?php


namespace Immo\Statements\Generation;


use Bitrix\Main;
use Immo;

/**
 * @description Трейт для запуска агента очереди
 * Trait Agent
 * @package Immo\Statements\Generation
 */
trait Agent
{
    /**
     * @var array Массив отпускных дней в году
     */
    public static array $yearHolidays = [];
    /**
     * @var array Массив выходных дней в неделе
     */
    public static array $weekHolidays = [];

    /**
     * @description Метод запуска очереди
     */
    public static function checkQueue(): void
    {
        $generation = new static(null);
        $generation->setLimit(static::LIMIT);
        $generation->workWithQueue();
    }

    /**
     * @description Метод для запуска в агенте
     * @return string
     */
    public static function run(): string
    {
        static::checkQueue();
        return "\\Immo\\Statements\\Generation\\Salary::run();";
    }

    /**
     * @description Запускает генерацию зарплатных ведомостей с проверкой даты запуска
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     */
    public static function generateZV()
    {
        if (static::checkExecution(new Main\Type\Date())) {
            (new static())->generate();
        }

        return '\\Immo\\Statements\\Generation\\Salary::generateZV();';
    }

    /**
     * @description Проверяет запуск генерации по дате
     * @param Main\Type\Date $curDate
     * @return bool
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     */
    protected static function checkExecution(Main\Type\Date $curDate): bool
    {
        if (
            !Main\Loader::includeModule('calendar')
            or !Main\Loader::includeModule('askaron.settings')
        ) {
            return false;
        }

        $generationDate = \Bitrix\Main\Config\Option::get(
            "askaron.settings",
            Immo\Manager::DATE_GENERATE_ZP_LIST
        );
        if (empty($generationDate)) {
            return false;
        }

        /**
         * Генерарует объект даты
         */
        $generationDate = (new Main\Type\Date())->setDate(
            $curDate->format('Y'),
            $curDate->format('n'),
            $generationDate
        );

        /**
         * Если дата генерации стоит на 31, а в текущем месяце дней меньше чем 31
         * Уменьшаем на последнюю дату в месяце
         */
        if ($generationDate->format('n') > $curDate->format('n')) {
            $lastDay = (new \DateTime($curDate->format('d.m.Y')))
                ->modify('last day of this month')
                ->format('d');

            $generationDate = (new Main\Type\Date())->setDate(
                $curDate->format('Y'),
                $curDate->format('n'),
                $lastDay
            );
        }

        /**
         * Если дата генерации в прошлом или равна текущей, то либо прошлую генерацию пропустили, либо запуск сегодня
         * Возращаем true
         */
        if ($generationDate == $curDate) {
            return true;
        }

        /**
         * Уменьшаем дату генерации на первую рабочую дату
         */
        while (
            static::isHoliday($generationDate)
            and $generationDate->format('n') == $curDate->format('n')
        ) {
            $generationDate->add('-1 day');
        }

        return $curDate->format('d.m.Y') == $generationDate->format('d.m.Y');
    }

    /**
     * @description Определяет, является ли переданный день выходным или праздничным днем
     * Метод скопирован из модуля bizproc
     * @see \CBPCalc::isHoliday()
     * @param Main\Type\Date $date
     * @return bool
     */
    public static function isHoliday(Main\Type\Date $date): bool
    {
        list($weekHolidays, $yearHolidays) = static::getCalendarHolidays();

        $dayOfWeek = $date->format('w');
        if (in_array($dayOfWeek, $weekHolidays)) {
            return true;
        }

        $dayOfYear = $date->format('j.n');
        if (in_array($dayOfYear, $yearHolidays, true)) {
            return true;
        }

        return false;
    }

    /**
     * @description Возвращает график выходных и праздничных дней
     * Метод скопирован из модуля bizproc
     * @see \CBPCalc::getCalendarHolidays()
     * @return array
     */
    public static function getCalendarHolidays(): array
    {
        if (empty(static::$yearHolidays)) {
            $calendarSettings = \CCalendar::GetSettings();
            $weekHolidays = [0, 6];
            $yearHolidays = [];

            if (isset($calendarSettings['week_holidays'])) {
                $weekDays = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
                $weekHolidays = [];
                foreach ($calendarSettings['week_holidays'] as $day) {
                    $weekHolidays[] = $weekDays[$day];
                }
            }

            if (isset($calendarSettings['year_holidays'])) {
                foreach (explode(',', $calendarSettings['year_holidays']) as $yearHoliday) {
                    $date = explode('.', trim($yearHoliday));
                    if (count($date) == 2 && $date[0] && $date[1]) {
                        $yearHolidays[] = (int)$date[0].'.'.(int)$date[1];
                    }
                }
            }

            static::$weekHolidays = $weekHolidays;
            static::$yearHolidays = $yearHolidays;
        }

        return [static::$weekHolidays, static::$yearHolidays];
    }
}