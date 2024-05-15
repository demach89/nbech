<?php

/**
 * Таргетная НБКИ-выгрузка по UID-договора на основе MSSQL
 */


include_once __DIR__ . '/../headers.php';

use Core\NBKI\NBKICommentator;
use Core\NBKI\NBKIExport;


$periodStart   = $periodStart   ?? '01.12.2023';
$portfolioCode = $portfolioCode ?? 'MSSQL';

$UID  = $UID ?? 'NOT_DEFINED'; // 'eccf0480-0dd5-1bc7-8b49-197fccec7267-3'

$nbkiExport = (new NBKIExport($periodStart, $portfolioCode))
    ->getFromMSSQL()
    ->loadByUID($UID)
    ->registryPrep()
    ->save();

$nbkiFileName = $nbkiExport->getNBKIFileName();

(new NBKICommentator($nbkiFileName))
    ->load()
    ->addComments()
    ->save();

return $nbkiFileName;