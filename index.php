<?php

error_reporting(0);
session_start();

include_once "./libs/func.php";


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['periodStart'] = $_SESSION['periodStart'] ?? $_COOKIE['periodStart'] ?? '';
    $_SESSION['periodEnd'] = $_SESSION['periodEnd'] ?? $_COOKIE['periodEnd'] ?? ' -------- ';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['periodStart'] = $_POST['periodStart'];
    setcookie('periodStart', $_SESSION['periodStart'], time() + 3600 * 24 * 365);

    if ($_POST['requestType'] === 'ALL') {
        $_SESSION['periodEnd'] = date('d.m.Y');
        setcookie('periodEnd', $_SESSION['periodEnd'], time() + 3600 * 24 * 365);
    }

    $reportType     = $_POST['reportType'];
    $requestType    = $_POST['requestType'];
    $periodStart    = date("d.m.Y", strtotime($_POST['periodStart']));
    $portfolioCode  = $_POST['requestType'];
    $contract       = $_POST['contract'];

    try {
        $outputFilePath = __DIR__ . "/nbki_files/"
            . match ($reportType) {
                'Google' => googleReport($requestType, $periodStart),
                'MSSQL'  => MSSQLReport($requestType, $periodStart, $contract),
            };

        fileOutput($outputFilePath);
    } catch (\Throwable $e) {
        die($e->getMessage());
    }
}

include __DIR__ . "/views/welcome.php";