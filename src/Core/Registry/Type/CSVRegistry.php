<?php

namespace Core\Registry\Type;
use Core\Provider\CSVImportProvider;
use Core\Registry\Registry;


/**
 * Класс подготовки выгрузки на основе CSV-файла
 * Class CSVRegistry
 * @package Core\Registry\Type
 */
class CSVRegistry extends Registry
{
    public function __construct(string $periodStart, string $portfolioCode)
    {
        parent::__construct();

        $this->periodStart   = $periodStart;
        $this->portfolioCode = $portfolioCode;
    }

    /**
     * Импорт данных реестра из CSV
     * @param string $fileName
     * @return $this
     */
    public function load(string $fileName) : self
    {
        try {
            $rowRegData = (new CSVImportProvider($fileName))->getData();
        } catch (\Throwable $e) {
            echo "CSVRegistry|load|Ошибка импорта реестра из CSV: {$e->getMessage()}\n";
            exit();
        }

        $this->regData = $this->assignRegData($rowRegData);

        return $this;
    }
}