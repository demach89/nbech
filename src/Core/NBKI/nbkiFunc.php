<?php

/**
 * Дополнительные НБКИ-функции
 */

/**
 * Перенос срочной задолженности в просроченную при наступлении даты возврата
 * @param array $regDatum
 * @param int $regTimestamp
 * @return void
 */
function srochkaToProsrochkaFix(array &$regDatum, int $regTimestamp) : void
{
    if (isCloseDatePassed($regDatum['closeDt'], $regTimestamp)) {
        // Увеличение просрочки ОД, ОП
        $regDatum['principalAmtPastDue'] += $regDatum['principalOutstanding'];
        $regDatum['intAmtPastDue']       += $regDatum['intOutstanding'];

        // Увеличение общей просрочки
        $regDatum['amtPastDue']      += ($regDatum['principalOutstanding'] + $regDatum['intOutstanding']);

        // Уменьшение общей срочки
        $regDatum['amtOutstanding']  -= ($regDatum['principalOutstanding'] + $regDatum['intOutstanding']);

        // Обнуление срочки
        $regDatum['principalOutstanding'] = 0;
        $regDatum['intOutstanding'] = 0;
    }
}

/**
 * Перерасчёт просрочки для закрытых:
 * Использовать 'просрочка_до_даты_последнего_платежа', если она меньше 'просрочка_табличная'
 * , где просрочка_табличная определяется самой таблицей - до даты суда (для просуженных), по текущий день (для непросуженных)
 *
 * @param array $regDatum
 */
function daysPastDueFix(array &$regDatum) : void
{
    $daysPastDueTable    = $regDatum['daysPastDue'];
    $daysPastDueUntilPay = ceil((strtotime($regDatum['paymtDate']) - strtotime($regDatum['pastDueDt']))/(60*60*24)) + 1; // отрицательно при отсутствии платежей

    if (($regDatum['amtOutstandingAll'] == 0) && ($daysPastDueUntilPay > 0) && ($daysPastDueTable > $daysPastDueUntilPay)) {
        $regDatum['daysPastDue'] = $daysPastDueUntilPay;
    }
}

/**
 * Проверка наступления даты возврата
 * @param string $closeDt
 * @param int $regTimestamp
 * @return bool
 */
function isCloseDatePassed(string $closeDt, int $regTimestamp) : bool
{
    $closeDtTimestamp = strtotime($closeDt);

    return ($regTimestamp >= $closeDtTimestamp);
}

/**
 * Проверка закрытия контракта
 * @param float $amtOutstandingAll
 * @return bool
 */
function isContractClosed(float $amtOutstandingAll) : bool
{
    return ($amtOutstandingAll == 0);
}

/**
 * Проверка на устарелость
 * @param array $regDatum
 * @param string $periodStart
 * @return bool
 */
function isOutdated(array $regDatum, string $periodStart/*, string $periodEnd*/) : bool
{
    $outdatedFlag = 0;

    $periodStartTimestamp = strtotime($periodStart);
    $lastPayDateTimestamp = strtotime($regDatum['paymtDate']);

    $restAll = $regDatum['amtOutstandingAll'];

    // Закрытие займа до начала установленного периода
    if (($restAll == 0) && ($lastPayDateTimestamp < $periodStartTimestamp)) {
        $outdatedFlag = 1;
    }

    return $outdatedFlag;
}

/**
 * Определить кол-во месяцев до закрытия (округление в большую сторону)
 * @param string $closeDt
 * @param int $regTimestamp
 * @return int
 */
function monthsToClose(string $closeDt, int $regTimestamp) : int
{
    $closeDtTimestamp = strtotime($closeDt);

    return ceil(($closeDtTimestamp - $regTimestamp) / (30*60*60*24));
}

