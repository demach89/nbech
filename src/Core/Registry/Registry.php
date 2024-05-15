<?php

namespace Core\Registry;

use Core\Assist\TableDataAssoc;
use Core\NBKI\NBKIDeleter;
use Core\NBKI\NBKIProcessor;
use Core\Saver\ANSISaver;

require_once __DIR__ . '/../NBKI/nbkiFunc.php';

/**
 * Класс подготовки выгрузки
 * Class Registry
 * @package Core\Registry
 */
abstract class Registry
{
    protected string $nbkiFileName;
    protected string $nbkiPath;
    protected array  $regData;
    protected array  $nbkiBlocks;
    protected int    $regTimestamp;

    protected string $periodStart;
    protected string $portfolioCode;

    use ANSISaver;

    public function __construct()
    {
        $this->regTimestamp = time();
    }

    /**
     * Создать массив данных реестра из сырых данных реестра провайдера
     * @param array $rowRegData
     * @return array
     * @throws \Exception
     */
    protected function assignRegData(array $rowRegData) : array
    {
        return TableDataAssoc::assocDataFromRaw($this->portfolioCode, $rowRegData);
    }

    /**
     * Корректировки дефолтные
     * @return $this
     */
    public function fix() : self
    {
        $this->bannedExclude();
        $this->outdatedFix();
        $this->srochkaToProsrochkaFix();
        $this->daysPastDueFix();

        return $this;
    }

            /** Исключить отозванные заявки (не имеют UID) */
            protected function bannedExclude() : void
            {
                $this->regData = array_filter($this->regData, function($datum) {
                    return ($datum['uuid'] !== '');
                });
            }

            /** Корректировка на соответствие заданному периоду */
            protected function outdatedFix() : void
            {
                $filtered = [];

                foreach ($this->regData as $regDatum) {
                    if (isOutdated($regDatum, $this->periodStart)) {
                        continue;
                    }

                    $filtered[] = $regDatum;
                }

                $this->regData = $filtered;
            }

            /** Конвертация срочной задолжености в просроченную при прошествии даты возврата */
            protected function srochkaToProsrochkaFix() : void
            {
                array_walk($this->regData, function (&$a){
                    srochkaToProsrochkaFix($a, $this->regTimestamp);

                });
            }

            /** Перерасчёт просрочки с учётом закрытия */
            protected function daysPastDueFix() : void
            {
                array_walk($this->regData, function (&$a){
                    daysPastDueFix($a);

                });
            }

    /** Подготовить выгрузку */
    public function registryPrep() : self
    {
        if (count($this->regData) === 0) {
            echo $msg = "Нет данных для выгрузки";
            error_log(__CLASS__ . "|" . __METHOD__ . "|" . $msg);
            exit();
        }

        try {
            $nbkiProcessor = new NBKIProcessor($this->regData, $this->regTimestamp);
        } catch (\Throwable $e) {
            echo $msg = "NBKIProcessor - ошибка подготовки выгрузки";
            error_log(__CLASS__ . "|" . __METHOD__ . "|" . $msg . "|" . $e->getMessage());
            exit();
        }

        $this->nbkiFileName = $nbkiProcessor->getFilename();
        $this->nbkiPath = NBKI_FILES_DIR . "\\" . $this->nbkiFileName;

        $this->nbkiBlocks = $nbkiProcessor->getNBKIBlocks();

        return $this;
    }

    /** Подготовить выгрузку по удалению заявки из НБКИ */
    public function registryPrepToDelete() : self
    {
        if (count($this->regData) === 0) {
            echo $msg = "Нет данных для выгрузки по удалению заявки из НБКИ";
            error_log(__CLASS__ . "|" . __METHOD__ . "|" . $msg);
            exit();
        }

        try {
            $nbkiProcessor = new NBKIDeleter($this->regData, $this->regTimestamp);
        } catch (\Throwable $e) {
            echo $msg = "NBKIProcessor - ошибка подготовки выгрузки по удалению заявки из НБКИ";
            error_log(__CLASS__ . "|" . __METHOD__ . "|" . $msg . "|" . $e->getMessage());
            exit();
        }

        $this->nbkiFileName = $nbkiProcessor->getFilename();
        $this->nbkiPath = NBKI_FILES_DIR . "\\" . $this->nbkiFileName;

        $this->nbkiBlocks = $nbkiProcessor->getNBKIBlocks();

        return $this;
    }

    /** Сохранить НБКИ-выгрузку в файл */
    public function save() : self
    {
        try {
            $this->rowsToTXT($this->nbkiBlocks, $this->nbkiPath);
        } catch (\Throwable $e){
            echo $msg = "Ошибка выгрузки";
            error_log(__CLASS__ . "|" . __METHOD__ . "|" . $msg . "|" . $e->getMessage());
            exit();
        }

        return $this;
    }

    /** Получить имя файла НБКИ-выгрузки */
    public function getNBKIFileName() : string
    {
        return $this->nbkiFileName;
    }
}