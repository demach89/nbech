<?php

/**
 * Массовая НБКИ-выгрузка на основе MSSQL
 */


include_once __DIR__ . '/../headers.php';

use Core\NBKI\NBKICommentator;
use Core\NBKI\NBKIExport;


$periodStart   = $periodStart   ?? 'DD.MM.YYYY';
$portfolioCode = $portfolioCode ?? 'MSSQL';

$nbkiExport = (new NBKIExport($periodStart, $portfolioCode))
    ->getFromMSSQL()
    ->loadAll()
    ->registryPrep()
    ->save();

$nbkiFileName = $nbkiExport->getNBKIFileName();

(new NBKICommentator($nbkiFileName))
    ->load()
    ->addComments()
    ->save();

return $nbkiFileName;