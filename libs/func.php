<?php

function fileOutput(string $filePath)
{
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    } else {
        throw new Error('Ошибка выгрузки');
    }
}

function isDateDMY(string $date) : bool
{
    $d = DateTime::createFromFormat('d.m.Y', $date);

    return ($d && $d->format('d.m.Y') === $date);
}

function getBeginPeriodDateFromPrompt()
{
    $beginPeriodDate = "";

    $argv = $_SERVER['argv'];
    $argc = count($argv);

    try {
        if ($argc < 2 ) {
            throw new Error("Не передана дата начала периода\n");
        }

        $beginPeriodDate = $argv[1];

        if (!isDateDMY($beginPeriodDate)) {
            throw new Error("Неверный формат даты\n");
        }
    } catch (\Throwable $e) {
        die($e->getMessage());
    }

    return $beginPeriodDate;
}

/**
 * Конвертировать дату формата ДД.ММ.ГГГГ в формат MSSQL
 * Отсчёт в MSSQL начинается с 01.01.1900
 *
 * @param string $date
 * @return int|void
 */
function dateToMSSQLFormat(string $date)
{
    try {
        $date1900   = new DateTime('01.01.1900');
        $neededDate = new DateTime($date);
        $daysDifference = $neededDate->diff($date1900);

        $MSSQLDate = $daysDifference->days + 2;
    } catch (\Throwable $e) {
        die("Неверный формат даты");
    }

    return $MSSQLDate;
}

/**
 * Конвертировать дату из формата MSSQL в ДД.ММ.ГГГГ
 * Отсчёт в MSSQL начинается с 01.01.1900
 *
 * @param int $MSSQLDate
 * @return string
 */
function MSSQLDateToNormalFormat(int $MSSQLDate) : string
{
    try {
        $MSSQLDate -= 2;
        $date1900 = new DateTime('01.01.1900');
        $interval = new DateInterval("P{$MSSQLDate}D");

        $normalDate = $date1900->add($interval)->format('d.m.Y');
    } catch (\Throwable $e) {
        die("Неверный формат даты");
    }

    return $normalDate;
}


/** Выгрузка портфелей "Google" */
function googleReport(string $requestType, string $periodStart) : string
{
    return match ($requestType) {
        'ALL'   => include(__DIR__ . '/../scripts/google_all_in_1.php'),
        'TARGET'=> include(__DIR__ . '/../scripts/google_target.php'),
        'DELETE'=> include(__DIR__ . '/../scripts/google_delete.php'),
        default => throw new Error('Неизвестный тип запроса'),
    };
}

/** Выгрузка портфелей "Арчи" */
function MSSQLReport(string $requestType, string $periodStart, string $contract) : string
{
    return match ($requestType) {
        'ALL'   => include(__DIR__ . '/../scripts/mssql_all_in_1.php'),
        'TARGET'=> ($contract)?
            include(__DIR__ . '/../scripts/mssql_target_contract.php') :
            die ('Договор не определён'),
        'DELETE'=> ($contract)?
            include(__DIR__ . '/../scripts/mssql_delete.php') :
            die ('Договор не определён'),
        default => throw new Error('Неизвестный тип запроса'),
    };
}