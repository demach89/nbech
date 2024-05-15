<?php

/**
 * НБКИ-выгрузка на основе Google-таблиц
 */


include_once __DIR__ . '/../headers.php';

use Core\NBKI\NBKICommentator;
use Core\NBKI\NBKIExport;


$periodStart   = $periodStart   ?? 'DD.MM.YYYY';
$portfolioCode = $portfolioCode ?? 'TARGET';

$nbkiExport = (new NBKIExport($periodStart, $portfolioCode))
    ->getFromGoogle()
    ->load()
    ->registryPrepToDelete()
    ->save();

$nbkiFileName = $nbkiExport->getNBKIFileName();

(new NBKICommentator($nbkiFileName))
    ->load()
    ->addComments()
    ->save();

return $nbkiFileName;