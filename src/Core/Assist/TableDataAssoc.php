<?php

namespace Core\Assist;
use Core\Portfolio\PortfolioServiceDefiner;

/**
 * Класс ассоциирования полей сырых табличных данных
 *
 * Class TableDataAssoc
 * @package Core\Assist
 */
class TableDataAssoc
{
    /**
     * Сопоставление данных
     * @param string $portfolioCode
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public static function assocDataFromRaw(string $portfolioCode, array $data) : array
    {
        $dataResult = [];

        $service = (new PortfolioServiceDefiner($portfolioCode))->define();

        switch ($service) {
            case 'google'   : $dataResult = self::google($data); break;
            case 'csv'      : $dataResult = self::csv($data); break;
            case 'mssql'    : $dataResult = self::mssql($data); break;
        }

        return $dataResult;
    }

    /**
     * Парсить методом 'google'
     * @param array $data
     * @return array
     */
    protected static function google(array $data) : array
    {
        $dataResult = [];
        try {
            foreach ($data as $datum) {
                array_unshift($datum, "zeroDummy"); // для удобства нумерации относительно номеров граф таблицы данных

                $dataResult[] = [
                    'globalNum'               => (int)$datum['globalNum'],                                      // №
                    //'clientPortfolioNum'    =>           (int)$datum['clientPortfolioNum'],                   // № КЛИЕНТА В ПОРТФЕЛЕ
                    //'calcActualRef'         =>        (string)$datum['calcActualRef'],                        // РАСЧЁТЫ КЛИЕНТА АКТУАЛЬНЫЕ
                    'portfolioName'           => (string)$datum['portfolioName'],                               // ПОРТФЕЛЬ НАЗВАНИЕ
                    //'portfolioRef'          =>        (string)$datum['portfolioRef'],                         // ПОРТФЕЛЬ ССЫЛКА
                    'acctNum'                 => VarPrep::str($datum['acctNum']),                               // НОМЕР ДОГОВОРА
                    'fio'                     => VarPrep::str($datum['fio']),                                   // ФИО КЛИЕНТА
                    'birthDt'                 => VarPrep::date($datum['birthDt']),                              // ДАТА РОЖДЕНИЯ КЛИЕНТА
                    'placeOfBirth'            => VarPrep::str($datum['placeOfBirth']),                          // МЕСТО РОЖДЕНИЯ КЛИЕНТА
                    'seriesNum'               => (string)$datum['seriesNum'],                                   // СЕРИЯ, № ПАСПОРТА
                    'issueDate'               => VarPrep::date($datum['issueDate']),                            // ДАТА ВЫДАЧИ ПАСПОРТА
                    'issueAuthority'          => VarPrep::str($datum['issueAuthority']),                        // КЕМ ВЫДАН ПАСПОРТ
                    'divCode'                 => VarPrep::str($datum['divCode']),                               // КОД ПОДРАЗДЕЛЕНИЯ
                    'SNILS'                   => VarPrep::SNILS($datum['SNILS']),                               // СНИЛС
                    'creditLimit'             => VarPrep::dgt($datum['creditLimit'], 2),                // СУММА ВЫДАЧИ
                    'openedDt'                => VarPrep::date($datum['openedDt']),                             // ДАТА ВЫДАЧИ ЗАЙМА
                    'closeDt'                 => VarPrep::date($datum['closeDt']),                              // ДАТА ОКОНЧАНИЯ ЗАЙМА
                    'daysCount'               => VarPrep::dgt($datum['daysCount'], 0),                  // СРОК ЗАЙМА (в днях)
                    'intRate'                 => VarPrep::dgt($datum['intRate'], 3),                    // ПРОЦЕНТНАЯ СТАВКА ГОДОВАЯ
                    'pastDueDt'               => VarPrep::date($datum['pastDueDt']),                            // ДАТА ВЫХОДА НА ПРОСРОЧКУ
                    'daysPastDue'             => VarPrep::dgt($datum['daysPastDue'], 0),                // КОЛИЧЕСТВО ДНЕЙ ПРОСРОЧКИ
                    'hasCard'                 => (string)$datum['hasCard'],                                     // ТИП ВЫДАЧИ ЗАЙМА
                    'isNovation'              => (int)$datum['isNovation'],                                     // НОВАЦИЯ ФАКТ
                    'creditTotalAmt'          => VarPrep::dgt($datum['creditTotalAmt'], 3),             // ПСК %
                    'creditTotalMonetaryAmt'  => VarPrep::dgt($datum['creditTotalMonetaryAmt'], 2),     // ПСК руб.
                    'principalOutstanding'    => VarPrep::dgt($datum['principalOutstanding'], 2),       // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНОМУ ОСНОВНОМУ ДОЛГУ
                    'principalAmtPastDue'     => VarPrep::dgt($datum['principalAmtPastDue'], 2),        // СУММА ЗАДОЛЖЕННОСТИ ПО ПРОСРОЧЕННОМУ ОСНОВНОМУ ДОЛГУ
                    'principalOutstandingAll' => VarPrep::dgt($datum['principalOutstandingAll'], 2),    // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ОСНОВНОМУ ДОЛГУ // в оригинале - principalOutstanding
                    'intOutstanding'          => VarPrep::dgt($datum['intOutstanding'], 2),             // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНЫМ ПРОЦЕНТАМ
                    'intAmtPastDue'           => VarPrep::dgt($datum['intAmtPastDue'], 2),              // СУММА ЗАДОЛЖЕННОСТИ ПО ПРОСРОЧЕННЫМ ПРОЦЕНТАМ
                    'intOutstandingAll'       => VarPrep::dgt($datum['intOutstandingAll'], 2),          // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ПРОЦЕНТАМ // в оригинале - intOutstanding
                    'otherAmtPastDue'         => VarPrep::dgt($datum['otherAmtPastDue'], 2),            // СУММА ЗАДОЛЖЕННОСТИ ПО ПЕНЯМ, ШТРАФАМ, НЕУСТОЙКЕ
                    'amtOutstanding'          => VarPrep::dgt($datum['amtOutstanding'], 2),             // ОБЩАЯ СУММА СРОЧНОЙ ЗАДОЛЖЕННОСТИ
                    'amtPastDue'              => VarPrep::dgt($datum['amtPastDue'], 2),                 // ОБЩАЯ СУММА ПРОСРОЧЕННОЙ ЗАДОЛЖЕННОСТИ
                    'amtOutstandingAll'       => VarPrep::dgt($datum['amtOutstandingAll'], 2),          // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ // в оригинале - amtOutstanding
                    'principalTotalAmt'       => VarPrep::dgt($datum['principalTotalAmt'], 2),          // ОПЛАТА ОСНОВНОГО ДОЛГА
                    'intTotalAmt'             => VarPrep::dgt($datum['intTotalAmt'], 2),                // ОПЛАТА ОСНОВНОГО ПРОЦЕНТА
                    'otherTotalAmt'           => VarPrep::dgt($datum['otherTotalAmt'], 2),              // ОПЛАТА ШТРАФНОГО ПРОЦЕНТА
                    'totalAmt'                => VarPrep::dgt($datum['totalAmt'], 2),                   // СУММА ПОЛУЧЕННЫХ ПЛАТЕЖЕЙ
                    'principalPaymtAmt'       => VarPrep::dgt($datum['principalPaymtAmt'], 2),          // ОПЛАТА ОСНОВНОГО ДОЛГА ПОСЛЕДНЯЯ
                    'intPaymtAmt'             => VarPrep::dgt($datum['intPaymtAmt'], 2),                // ОПЛАТА ОСНОВНОГО ПРОЦЕНТА ПОСЛЕДНЯЯ
                    'otherPaymtAmt'           => VarPrep::dgt($datum['otherPaymtAmt'], 2),              // ОПЛАТА ШТРАФНОГО ПРОЦЕНТА ПОСЛЕДНЯЯ
                    'paymtAmt'                => VarPrep::dgt($datum['paymtAmt'], 2),                   // СУММА ПОСЛЕДНЕГО ПЛАТЕЖА
                    'paymtDate'               => VarPrep::date($datum['paymtDate']),                            // ДАТА ПОСЛЕДНЕГО ПЛАТЕЖА
                    'uuid'                    => (string)$datum['uuid'],                                        // UID
                    'lastPaymentDueCode'      => '',                                                            // ПРИЧИНА ОТПРАВКИ // Признак расчета по последнему платежу
                    'calcDate'                => null,                                                          // ДАТА РАСЧЁТА
                ];
            }
        } catch (\Throwable $e) {
            die('Ошибка данных: проверьте корректность загрузки таблицы');
        }

        return $dataResult;
    }

    /**
     * Парсить методом 'csv'
     * @param array $data
     * @return array
     */
    protected static function csv(array $data) : array
    {
        $dataResult = [];

        foreach ($data as $datum) {
            array_unshift($datum, "zeroDummy"); // для удобства нумерации относительно номеров граф таблицы данных

            $dataResult[] = [
                'globalNum'                 =>          (int)$datum[1],                  // №
                //'clientPortfolioNum'      =>      (int)$datum[2],                      // № КЛИЕНТА В ПОРТФЕЛЕ
                //'calcActualRef'           =>   (string)$datum[3],                      // РАСЧЁТЫ КЛИЕНТА АКТУАЛЬНЫЕ
                //'portfolioName'           =>   (string)$datum[4],                      // ПОРТФЕЛЬ НАЗВАНИЕ
                //'portfolioRef'            =>   (string)$datum[5],                      // ПОРТФЕЛЬ ССЫЛКА
                'acctNum'                   =>   VarPrep::str($datum[2]),                // НОМЕР ДОГОВОРА
                'fio'                       =>   VarPrep::str($datum[3]),                // ФИО КЛИЕНТА
                'birthDt'                   =>   VarPrep::date($datum[4]),               // ДАТА РОЖДЕНИЯ КЛИЕНТА
                'placeOfBirth'              =>   VarPrep::str($datum[5]),                // МЕСТО РОЖДЕНИЯ КЛИЕНТА
                'seriesNum'                 =>        (string)$datum[6],                 // СЕРИЯ, № ПАСПОРТА
                'issueDate'                 =>   VarPrep::date($datum[7]),               // ДАТА ВЫДАЧИ ПАСПОРТА
                'issueAuthority'            =>   VarPrep::str($datum[8]),                // КЕМ ВЫДАН ПАСПОРТ
                'divCode'                   =>   VarPrep::str($datum[9]),                // КОД ПОДРАЗДЕЛЕНИЯ
                'SNILS'                     =>   VarPrep::SNILS($datum[10]),             // СНИЛС
                'creditLimit'               =>   VarPrep::dgt($datum[11], 2),    // СУММА ВЫДАЧИ
                'openedDt'                  =>   VarPrep::date($datum[12]),              // ДАТА ВЫДАЧИ ЗАЙМА
                'closeDt'                   =>   VarPrep::date($datum[13]),              // ДАТА ОКОНЧАНИЯ ЗАЙМА
                'daysCount'                 =>   VarPrep::dgt($datum[14],0),     // СРОК ЗАЙМА (в днях)
                'intRate'                   =>   VarPrep::dgt($datum[15],3),     // ПРОЦЕНТНАЯ СТАВКА ГОДОВАЯ
                'pastDueDt'                 =>   VarPrep::date($datum[16]),              // ДАТА ВЫХОДА НА ПРОСРОЧКУ
                'daysPastDue'               =>   VarPrep::dgt($datum[17],0),     // КОЛИЧЕСТВО ДНЕЙ ПРОСРОЧКИ
                'hasCard'                   =>        (string)$datum[18],                // ТИП ВЫДАЧИ ЗАЙМА
                'isNovation'                =>           (int)$datum[19],                // НОВАЦИЯ ФАКТ
                'creditTotalAmt'            =>   VarPrep::dgt($datum[20], 3),    // ПСК %
                'creditTotalMonetaryAmt'    =>   VarPrep::dgt($datum[21], 2),    // ПСК руб.
                'principalOutstanding'      =>   VarPrep::dgt($datum[22], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНОМУ ОСНОВНОМУ ДОЛГУ
                'principalAmtPastDue'       =>   VarPrep::dgt($datum[23], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО ПРОСРОЧЕННОМУ ОСНОВНОМУ ДОЛГУ
                'principalOutstandingAll'   =>   VarPrep::dgt($datum[24], 2),    // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ОСНОВНОМУ ДОЛГУ // в оригинале - principalOutstanding
                'intOutstanding'            =>   VarPrep::dgt($datum[25], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНЫМ ПРОЦЕНТАМ
                'intAmtPastDue'             =>   VarPrep::dgt($datum[26], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО ПРОСРОЧЕННЫМ ПРОЦЕНТАМ
                'intOutstandingAll'         =>   VarPrep::dgt($datum[27], 2),    // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ПРОЦЕНТАМ // в оригинале - intOutstanding
                'otherAmtPastDue'           =>   VarPrep::dgt($datum[28], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО ПЕНЯМ, ШТРАФАМ, НЕУСТОЙКЕ
                'amtOutstanding'            =>   VarPrep::dgt($datum[29], 2),    // ОБЩАЯ СУММА СРОЧНОЙ ЗАДОЛЖЕННОСТИ
                'amtPastDue'                =>   VarPrep::dgt($datum[30], 2),    // ОБЩАЯ СУММА ПРОСРОЧЕННОЙ ЗАДОЛЖЕННОСТИ
                'amtOutstandingAll'         =>   VarPrep::dgt($datum[31], 2),    // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ // в оригинале - amtOutstanding
                'principalTotalAmt'         =>   VarPrep::dgt($datum[32], 2),    // ОПЛАТА ОСНОВНОГО ДОЛГА
                'intTotalAmt'               =>   VarPrep::dgt($datum[33], 2),    // ОПЛАТА ОСНОВНОГО ПРОЦЕНТА
                'otherTotalAmt'             =>   VarPrep::dgt($datum[34], 2),    // ОПЛАТА ШТРАФНОГО ПРОЦЕНТА
                'totalAmt'                  =>   VarPrep::dgt($datum[35], 2),    // СУММА ПОЛУЧЕННЫХ ПЛАТЕЖЕЙ
                'principalPaymtAmt'         =>   VarPrep::dgt($datum[36], 2),    // ОПЛАТА ОСНОВНОГО ДОЛГА ПОСЛЕДНЯЯ
                'intPaymtAmt'               =>   VarPrep::dgt($datum[37], 2),    // ОПЛАТА ОСНОВНОГО ПРОЦЕНТА ПОСЛЕДНЯЯ
                'otherPaymtAmt'             =>   VarPrep::dgt($datum[38], 2),    // ОПЛАТА ШТРАФНОГО ПРОЦЕНТА ПОСЛЕДНЯЯ
                'paymtAmt'                  =>   VarPrep::dgt($datum[39], 2),    // СУММА ПОСЛЕДНЕГО ПЛАТЕЖА
                'paymtDate'                 =>   VarPrep::date($datum[40]),              // ДАТА ПОСЛЕДНЕГО ПЛАТЕЖА
                'uuid'                      =>         (string)$datum[41],               // UID
                'lastPaymentDueCode'        =>                '',                        // ПРИЧИНА ОТПРАВКИ // Признак расчета по последнему платежу
                'calcDate'                  =>              null,                        // ДАТА РАСЧЁТА
            ];
        }

        return $dataResult;
    }


    /**
     * Парсить методом 'mssql'
     * @param array $data
     * @return array
     */
    protected static function mssql(array $data) : array
    {
        $dataResult = [];

        foreach ($data as $datum) {
            array_unshift($datum, "zeroDummy"); // для удобства нумерации относительно номеров граф таблицы данных

            $dataResult[] = [
                'globalNum'                 =>           (int)$datum['id'],                         // №
                'acctNum'                   =>   VarPrep::str($datum['contract']),                  // НОМЕР ДОГОВОРА
                'fio'                       =>   VarPrep::str($datum['fio']),                       // ФИО КЛИЕНТА
                'birthDt'                   =>   VarPrep::date($datum['birthDate']),                // ДАТА РОЖДЕНИЯ КЛИЕНТА
                'placeOfBirth'              =>   VarPrep::str($datum['birthPlace']),                // МЕСТО РОЖДЕНИЯ КЛИЕНТА
                'seriesNum'                 =>        (string)$datum['docSerialNum'],               // СЕРИЯ, № ПАСПОРТА
                'issueDate'                 =>   VarPrep::date($datum['docBeginDate']),             // ДАТА ВЫДАЧИ ПАСПОРТА
                'issueAuthority'            =>   VarPrep::str($datum['docEmit']),                   // КЕМ ВЫДАН ПАСПОРТ
                'divCode'                   =>   VarPrep::str($datum['docCode']),                   // КОД ПОДРАЗДЕЛЕНИЯ
                'SNILS'                     =>   VarPrep::SNILS($datum['snils']),                   // СНИЛС
                'creditLimit'               =>   VarPrep::dgt($datum['costall'], 2),        // СУММА ВЫДАЧИ
                'openedDt'                  =>   VarPrep::date($datum['putDate']),                  // ДАТА ВЫДАЧИ ЗАЙМА
                'closeDt'                   =>   VarPrep::date($datum['closeDate']),                // ДАТА ОКОНЧАНИЯ ЗАЙМА
                'daysCount'                 =>   VarPrep::dgt($datum['daysCount'],0),       // СРОК ЗАЙМА (в днях)
                'intRate'                   =>   VarPrep::dgt($datum['rateGod'],3),         // ПРОЦЕНТНАЯ СТАВКА ГОДОВАЯ
                'pastDueDt'                 =>   VarPrep::date($datum['shtrafBeginDate']),          // ДАТА ВЫХОДА НА ПРОСРОЧКУ
                'daysPastDue'               =>   VarPrep::dgt($datum['shtrafDaysCount'],0), // КОЛИЧЕСТВО ДНЕЙ ПРОСРОЧКИ
                'hasCard'                   =>        (string)$datum['putType'],                    // ТИП ВЫДАЧИ ЗАЙМА
                'isNovation'                =>           (int)$datum['novation'],                   // НОВАЦИЯ ФАКТ
                'creditTotalAmt'            =>   VarPrep::dgt($datum['psk'], 3),            // ПСК %
                'creditTotalMonetaryAmt'    =>   VarPrep::dgt($datum['pskRub'], 2),         // ПСК руб.
                'principalOutstanding'      =>   VarPrep::dgt($datum['restODSroch'], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНОМУ ОСНОВНОМУ ДОЛГУ
                'principalAmtPastDue'       =>   VarPrep::dgt($datum['restODProsr'], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО ПРОСРОЧЕННОМУ ОСНОВНОМУ ДОЛГУ
                'principalOutstandingAll'   =>   VarPrep::dgt($datum['restODAll'], 2),      // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ОСНОВНОМУ ДОЛГУ // в оригинале - principalOutstanding
                'intOutstanding'            =>   VarPrep::dgt($datum['restOPSroch'], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНЫМ ПРОЦЕНТАМ
                'intAmtPastDue'             =>   VarPrep::dgt($datum['restOPProsr'], 2),    // СУММА ЗАДОЛЖЕННОСТИ ПО ПРОСРОЧЕННЫМ ПРОЦЕНТАМ
                'intOutstandingAll'         =>   VarPrep::dgt($datum['restOPAll'], 2),      // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ПРОЦЕНТАМ // в оригинале - intOutstanding
                'otherAmtPastDue'           =>   VarPrep::dgt($datum['restPeny'], 2),       // СУММА ЗАДОЛЖЕННОСТИ ПО ПЕНЯМ, ШТРАФАМ, НЕУСТОЙКЕ
                'amtOutstanding'            =>   VarPrep::dgt($datum['restSroch'], 2),      // ОБЩАЯ СУММА СРОЧНОЙ ЗАДОЛЖЕННОСТИ
                'amtPastDue'                =>   VarPrep::dgt($datum['restProsr'], 2),      // ОБЩАЯ СУММА ПРОСРОЧЕННОЙ ЗАДОЛЖЕННОСТИ
                'amtOutstandingAll'         =>   VarPrep::dgt($datum['restAll'], 2),        // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ // в оригинале - amtOutstanding
                'principalTotalAmt'         =>   VarPrep::dgt($datum['paysOD'], 2),         // ОПЛАТА ОСНОВНОГО ДОЛГА
                'intTotalAmt'               =>   VarPrep::dgt($datum['paysOP'], 2),         // ОПЛАТА ОСНОВНОГО ПРОЦЕНТА
                'otherTotalAmt'             =>   VarPrep::dgt($datum['paysPeny'], 2),       // ОПЛАТА ШТРАФНОГО ПРОЦЕНТА
                'totalAmt'                  =>   VarPrep::dgt($datum['paysAll'], 2),        // СУММА ПОЛУЧЕННЫХ ПЛАТЕЖЕЙ
                'principalPaymtAmt'         =>   VarPrep::dgt($datum['payODLast'], 2),      // ОПЛАТА ОСНОВНОГО ДОЛГА ПОСЛЕДНЯЯ
                'intPaymtAmt'               =>   VarPrep::dgt($datum['payOPLast'], 2),      // ОПЛАТА ОСНОВНОГО ПРОЦЕНТА ПОСЛЕДНЯЯ
                'otherPaymtAmt'             =>   VarPrep::dgt($datum['payPenyLast'], 2),    // ОПЛАТА ШТРАФНОГО ПРОЦЕНТА ПОСЛЕДНЯЯ
                'paymtAmt'                  =>   VarPrep::dgt($datum['payAllLast'], 2),     // СУММА ПОСЛЕДНЕГО ПЛАТЕЖА
                'paymtDate'                 =>   VarPrep::date($datum['payLastDate']),              // ДАТА ПОСЛЕДНЕГО ПЛАТЕЖА
                'uuid'                      =>        (string)$datum['uid'],                        // UID
                'lastPaymentDueCode'        =>           '', //(int)$datum['formReason'],           // ПРИЧИНА ОТПРАВКИ // Признак расчета по последнему платежу
                'calcDate'                  =>   VarPrep::MSSQLDate($datum['formDate']),            // ДАТА РАСЧЁТА
            ];
        }

        return $dataResult;
    }
}