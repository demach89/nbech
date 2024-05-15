<?php

namespace Core\NBKI;

use Core\Registry\Registry;
use Core\Registry\Type\MSSQLRegistry;
use Core\Registry\Type\CSVRegistry;
use Core\Registry\Type\GoogleAppRegistry;

/**
 * Класс-обёртка НБКИ-выгрузки
 * Class NBKIExport
 * @package Core\NBKI
 */
class NBKIExport
{
    protected string $periodStart;
    protected string $portfolioCode;

    public function __construct(string $periodStart, string $portfolioCode)
    {
        $this->periodStart   = $periodStart;
        $this->portfolioCode = $portfolioCode;
    }

    /**
     * Получить НБКИ-выгрузку на основе CSV
     * @return Registry
     */
    public function getFromCSV() : Registry
    {
        return new CSVRegistry($this->periodStart, $this->portfolioCode);
    }

    /**
     * Получить НБКИ-выгрузку на основе GoogleApp
     */
    public function getFromGoogle() : Registry
    {
        return new GoogleAppRegistry($this->periodStart, $this->portfolioCode);
    }

    /**
     * Получить НБКИ-выгрузку на основе MSSQL
     */
    public function getFromMSSQL() : Registry
    {
        return new MSSQLRegistry($this->periodStart, $this->portfolioCode);
    }

}