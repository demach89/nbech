<?php

namespace Core\Provider;

/**
 * Поставщик данных - CSV
 * Class CSVImportProvider
 * @package Core\Provider
 */
class CSVImportProvider
{
    protected string $CSVFilePath;
    protected array  $CSVData;
    protected string $separator;

    public function __construct(string $CSVFileName, string $separator = ';')
    {
        $this->CSVFilePath = __DIR__ . "/../../../" . CSV_IMPORT_DIR . "/$CSVFileName";

        $this->separator = $separator;

        try {
            $this->importCSV();
        } catch (\Throwable $e) {
            echo "Проблема с импортом CSV: {$e->getMessage()}\n";
            exit();
        }
    }

    /**
     * Импорт данных для выгрузки из CSV
     * Конвертация в UTF-8 позволяет избежать смешивания кодировок, например:
     *    стандартный CSV в ANSI кодировке, при последующем добавлении в массив с ANSI-строками вручную введённые строки,
     *    типа 'Блок' (в ч.н. helper), при выгрузке файл становится нечитаемым (смешанные кодировки).
     *    Т.о. при едином формате данных (UTF-8) выгрузка будет всегда приводится к нужному формату (ANSI)
     */
    protected function importCSV() : void
    {
        $content = file_get_contents($this->CSVFilePath);

        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = iconv("CP1251//TRANSLIT", "UTF-8", $content);
        }

        $csv = explode(PHP_EOL, $content);

        $csv = $this->filterCSV($csv);

        array_walk($csv, function (&$a){
            $a = explode($this->separator, $a);
        });

        $this->CSVData = $csv;
    }

    /**
     * Убрать лишние строки (пустые, заголовкий)
     * @param array $csv
     * @return array
     */
    protected function filterCSV(array $csv) : array
    {
        $csv = array_filter($csv); // убирает пустые строки (актуально для концовки)

        $csv = array_filter(       // убирает пустые строки с разделителем (актуально для концовки)
            $csv,
            fn($str) => !preg_match("/^[$this->separator]+$/u", $str)
        );

        array_shift($csv); // убирает заголовки
        //array_shift($csv); // убирает номера столбцов

        return $csv;
    }

    public function getData() : array
    {
        return $this->CSVData;
    }
}