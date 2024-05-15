<?php

/**
 * Таргетная НБКИ-выгрузка по номеру договора на основе MSSQL, для удаления
 */


include_once __DIR__ . '/../headers.php';

use Core\NBKI\NBKICommentator;
use Core\NBKI\NBKIExport;


$periodStart   = $periodStart   ?? '01.12.2023';
$portfolioCode = $portfolioCode ?? 'MSSQL';

$contract  = $contract ?? 'NOT_DEFINED'; // 16-01-2022-0021-13 // 'NOT_DEFINED'

$nbkiExport = (new NBKIExport($periodStart, $portfolioCode))
    ->getFromMSSQL()
    ->loadByContract($contract)
    ->registryPrepToDelete()
    ->save()
    ->excludeContract($contract);

$nbkiFileName = $nbkiExport->getNBKIFileName();

(new NBKICommentator($nbkiFileName))
    ->load()
    ->addComments()
    ->save();

return $nbkiFileName;