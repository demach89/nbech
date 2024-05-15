<?php

/**
 * Подготовка НБКИ-выгрузки на основе CSV
 */


include_once __DIR__ . '/../headers.php';

use Core\NBKI\NBKICommentator;
use Core\NBKI\NBKIExport;


$CSVFileName   = 'csv_example.csv';
$periodStart   = $periodStart   ?? 'DD.MM.YYYY';
$portfolioCode = $portfolioCode ?? 'CSV';

$nbkiExport = (new NBKIExport($periodStart, $portfolioCode))
    ->getFromCSV()
    ->load($CSVFileName)
    ->fix()
    ->registryPrep()
    ->save();

$nbkiFileName = $nbkiExport->getNBKIFileName();

(new NBKICommentator($nbkiFileName))
    ->load()
    ->addComments()
    ->save();

return $nbkiFileName;
