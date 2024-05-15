<?php

namespace Core\Saver;

/**
 * Экспортер массива данных в TXT-файл кодировки ANSI
 * Trait ANSISaver
 * @package Core\Saver
 */
Trait ANSISaver
{
    /**
     * Сохранить массив данных в TXT-файл
     */
    protected function rowsToTXT(array $data, string $filePath) : void
    {
        $fp = fopen($filePath, 'w');

        foreach ($data as $datum) {
            fwrite($fp, implode("\t", $datum)."\n");
        }

        fclose($fp);

        $this->UTF8toANSI($filePath);
    }

    /**
     * Конвертировать TXT-файл из UTF-8 в ANSI
     */
    protected function UTF8toANSI(string $filePath) : void
    {
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);

            if (mb_check_encoding($content, 'UTF-8')) {
                $content = iconv("UTF-8", "CP1251//TRANSLIT", $content);
                file_put_contents($filePath, $content);
            }
        } else {
            echo "UTF8toANSI: Исходный файл не существует.";
            exit();
        }
    }

}