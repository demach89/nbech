<?php

namespace Core\Assist;
use DateTime;

require_once __DIR__ . "/../../../libs/func.php";

/**
 * Класс подготовки переменных к использованию
 *
 * Class VarPrep
 * @package Core\Assist
 */
class VarPrep
{
    /**
     * Конвертация табличной стоки числа в числовой формат для возможности работы с числами
     * - устранение пробелов
     * - замена разделителя запятой на точку
     * - приведение к дроби с установленной точностью
     * @param string $dgtStr
     * @param int $precision
     * @return string
     */
    public static function dgt(string $dgtStr, int $precision) : string
    {
        $dgt = (float)str_replace([' ', ','], ['', '.'], $dgtStr);

        return round(
            $dgt,
            $precision
        );
    }

    /**
     * Конвертация числа в НБКИ-формат числа (пр: 1234,56)
     * - замена разделителя точки на запятую
     * - установка точности для дробной части
     * @param float $dgt
     * @param int $precision
     * @param string $separator
     * @return string
     */
    public static function nbkiDgt(float $dgt, int $precision = 2, string $separator = ',') : string
    {
        return number_format(
            $dgt,
            $precision,
            $separator,
            ''
        );
    }

    /**
     * Конвертация строковой переменной таблицы в строковый формат
     * - трим
     * - устранение двойных пробелов
     * - корректировка дефисов
     * @param string $strRow
     * @return string
     */
    public static function str(string $strRow) : string
    {
        $str = trim($strRow);
        $str = str_replace('  ', ' ', $str);
        $str = str_replace([' - ', '- ', ' -'], '-', $str);
        $str = mb_strtoupper($str);

        return $str;
    }

    /**
     * Преобразование табличной строки даты в допустимый формат
     * Допустимый формат табличной строки даты: дд.мм.гггг, ISO 8601
     * @param string $date
     * @return string
     */
    public static function date(string $date) : string
    {
        return match (self::isDateDMY($date) || self::isDateISO_8601($date)) {
            true  => date('d.m.Y', strtotime($date)),
            false => ''
        };
    }

            /**
             *  Проверка формата даты на дд.мм.ГГГГ
             * @param string $date
             * @return bool
             */
            protected static function isDateDMY(string $date) : bool
            {
                $d = DateTime::createFromFormat('d.m.Y', $date);

                return ($d && $d->format('d.m.Y') === $date);
            }

            /**
             * Проверка формата даты на ISO 8601 date (ex 2004-02-12T15:19:21+00:00)
             * @param string $date
             * @return bool
             */
            protected static function isDateISO_8601(string $date) : bool
            {
                return strtotime($date);
            }


    /**
     * Конвертация строки СНИЛС в приемлимый для НБКИ цифоровой формат
     * @param string $SNILS
     * @return string
     */
    public static function SNILS(string $SNILS) : string
    {
        return str_replace([' ', '-'], '', $SNILS);
    }

    /**
     * Преобразование табличной строки даты в допустимый формат (устаревшая версия)
     * @param string $date
     * @return string
     */
    public static function date_OLD(string $date) : string
    {
        $date = trim($date);

        $d = DateTime::createFromFormat('d.m.Y', $date);

        return match ($d && $d->format('d.m.Y') === $date) {
            true  => $date,
            false => ''
        };
    }

    /**
     * Конвертация даты из формата MSSQL в ДД.ММ.ГГГГ
     * @param int $MSSQLDate
     * @return string
     */
    public static function MSSQLDate(int $MSSQLDate) : string
    {
        return MSSQLDateToNormalFormat($MSSQLDate);
    }
}