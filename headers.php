<?php

header('Content-Type: text/html; charset=utf-8');

date_default_timezone_set('Asia/Tomsk');

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include_once __DIR__ . '/autoload.php';
include_once __DIR__ . '/src/Core/env.php';