<?php

namespace Core\Registry\Type;
use Core\Provider\GoogleAppProvider;
use Core\Registry\Registry;


/**
 * Класс подготовки выгрузки на основе GoogleApp
 * Class GoogleAppRegistry
 * @package Core\Registry\Type
 */
class GoogleAppRegistry extends Registry
{
    public function __construct(string $periodStart, string $portfolioCode)
    {
        parent::__construct();

        $this->periodStart   = $periodStart;
        $this->portfolioCode = $portfolioCode;

        $this->regData = [];
    }

    /**
     * Импорт данных реестра из GoogleApp
     * @return $this
     * @throws \Exception
     */
    public function load() : self
    {
        try {
            $rowRegData = (new GoogleAppProvider($this->portfolioCode, $this->periodStart))->getData();
        } catch (\Throwable $e) {
            echo __CLASS__ . "|" . __FUNCTION__  . "|Ошибка импорта данных|{$e->getMessage()}\n";
            exit();
        }

        $this->regData = $this->assignRegData($rowRegData);

        return $this;
    }

    /**
     * Импорт данных всех реестров из GoogleApp
     * @return $this
     * @throws \Exception
     */
    public function loadAll() : self
    {
        $portfolioCodes = array_keys(NBKI_SERVICE_GOOGLE);

        foreach ($portfolioCodes as $portfolioCode) {
            $this->portfolioCode = $portfolioCode;

            try {
                $rowRegData = (new GoogleAppProvider($this->portfolioCode, $this->periodStart))->getData();
            } catch (\Throwable $e) {
                echo __CLASS__ . "|" . __FUNCTION__  . "|Ошибка импорта данных|{$e->getMessage()}\n";
                exit();
            }

            $regData = array_merge($this->regData, $this->assignRegData($rowRegData));

            $this->regData = $regData;
        }

        return $this;
    }
}