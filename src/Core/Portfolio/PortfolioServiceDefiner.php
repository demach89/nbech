<?php

namespace Core\Portfolio;

require_once __DIR__ . '/../env.php';

/**
 * Класс определения сервиса, обслуживающего портфель
 * Сервис — Google-таблица, содержащая данные портфеля
 *
 * Class PortfolioServiceDefiner
 * @package Core\Portfolio
 */
class PortfolioServiceDefiner
{
    protected string $portfolioCode;

    public function __construct(string $portfolioCode)
    {
        $this->portfolioCode = $portfolioCode;
    }

    /**
     * Определить сервис
     * @return string
     * @throws \Exception
     */
    public function define() : string
    {
        $service = 'service_no_defined';

        switch ($this->portfolioCode) {
            case 'TARGET'                        :
            case ($this->isNBKI_SERVICE_GOOGLE())  : $service = 'google'; break;
            case ($this->isNBKI_SERVICE_CSV())   : $service = 'csv'; break;
            case ($this->isNBKI_SERVICE_MSSQL()) : $service = 'mssql'; break;
        }

        if ($service === 'service_no_defined') {
            throw new \Exception(__CLASS__ . "|" . __FUNCTION__ . "|Сервис не определён");
        }

        return $service;
    }

    /**
     * Принадлежность сервиса к NBKI_SERVICE_1
     * @return bool
     */
    protected function isNBKI_SERVICE_GOOGLE() : bool
    {
        return array_key_exists(
            $this->portfolioCode,
            NBKI_SERVICE_GOOGLE
        ) || $this->portfolioCode === 'VIVA_ALL';
    }

    /**
     * Принадлежность сервиса к NBKI_SERVICE_CSV
     * @return bool
     */
    protected function isNBKI_SERVICE_CSV() : bool
    {
        return array_key_exists(
            $this->portfolioCode,
            NBKI_SERVICE_CSV
        );
    }

    /**
     * Принадлежность сервиса к NBKI_SERVICE_MSSQL
     * @return bool
     */
    protected function isNBKI_SERVICE_MSSQL() : bool
    {
        return array_key_exists(
            $this->portfolioCode,
            NBKI_SERVICE_MSSQL
        );
    }
}