<?php

/**
 * Массовая НБКИ-выгрузка на основе Google-таблиц, все портфели в одном файле
 */


include_once __DIR__ . '/../headers.php';

use Core\NBKI\NBKICommentator;
use Core\NBKI\NBKIExport;


$periodStart   = $periodStart   ?? 'DD.MM.YYYY';
$portfolioCode = $portfolioCode ?? 'ALL';

$periodStart   = $periodStart   ?? '01.12.2023';
$portfolioCode = $portfolioCode ?? 'VIVA_ALL';

$nbkiExport = (new NBKIExport($periodStart, $portfolioCode))
    ->getFromGoogle()
    ->load()
    ->fix()
    ->registryPrep()
    ->save();

$nbkiFileName = $nbkiExport->getNBKIFileName();

(new NBKICommentator($nbkiFileName))
    ->load()
    ->addComments()
    ->save();

return $nbkiFileName;