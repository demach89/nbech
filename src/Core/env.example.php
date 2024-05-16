<?php

const CSV_IMPORT_DIR = "csv_import";
const NBKI_FILES_DIR = "nbki_files";

const NBKI_CUSTOMER_CODE = "XXX";
const NBKI_CUSTOMER_PWD  = "XXX";
const NBKI_CUSTOMER_INN  = "XXX";
const NBKI_CUSTOMER_ORGN = "XXX";

const NBKI_RUTDF_VER = "RUTDF6.0";

const GOOGLE_WEB_APPS = [
    "google" => "https://script.google.com/macros/s/XXX/exec"
];

const NBKI_SERVICE_GOOGLE = [
    'PORTFOLIO1' => [
        'name' => 'Портфель 1',
        'date' => 'DD.MM.YYYY',
        'code' => 'PORTFOLIO1',
    ],
    'PORTFOLIO2' => [
        'name' => 'Портфель 2',
        'date' => 'DD.MM.YYYY',
        'code' => 'PORTFOLIO2',
    ],
    'PORTFOLIO3' => [
        'name' => 'Портфель 3',
        'date' => 'DD.MM.YYYY',
        'code' => 'PORTFOLIO3',
    ],
];

const NBKI_SERVICE_CSV = [
    'CSV' => [
        'name' => 'CSV',
        'date' => 'DD.MM.YYYY',
        'code' => 'CSV',
    ],
];

const NBKI_SERVICE_MSSQL = [
    'MSSQL' => [
        'name' => 'MSSQL',
        'ref'  => '',
        'date' => 'DD.MM.YYYY',
        'code' => 'MSSQL',
    ],
];

const MSSQL_CRED = [
    'address' => 'MSSQL_SERVER_NAME, PORT',
    'dbname' => 'dbname',
    'UID' => 'user',
    'PWD' => 'pwd',
];
