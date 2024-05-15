<?php

namespace Core\Registry\Type;
use Core\Provider\MSSQLProvider;
use Core\Registry\Registry;

/**
 * Класс подготовки выгрузки на основе MSSQL
 * Class MSSQLRegistry
 * @package Core\Registry\Type
 */
class MSSQLRegistry extends Registry
{
    public function __construct(string $periodStart, string $portfolioCode)
    {
        parent::__construct();

        $this->periodStart   = $periodStart;
        $this->portfolioCode = $portfolioCode;

        $this->regData = [];
    }

    /**
     * Получить все
     * @return $this
     * @throws \Exception
     */
    public function loadAll() : self
    {
        try {
            $rowRegData = (new MSSQLProvider())->get();
        } catch (\Throwable $e) {
            echo __CLASS__ . "|" . __FUNCTION__  . "|Ошибка импорта данных|{$e->getMessage()}\n";
            exit();
        }

        $regData = array_merge($this->regData, $this->assignRegData($rowRegData));

        $this->regData = $regData;

        return $this;
    }

    /**
     * Получить с даты начала периода
     * @return $this
     * @throws \Exception
     */
    public function loadAfterDate() : self
    {
        try {
            $rowRegData = (new MSSQLProvider())->getAfterDate($this->periodStart);
        } catch (\Throwable $e) {
            echo __CLASS__ . "|" . __FUNCTION__  . "|Ошибка импорта данных|{$e->getMessage()}\n";
            exit();
        }

        $regData = array_merge($this->regData, $this->assignRegData($rowRegData));

        $this->regData = $regData;

        return $this;
    }

    /**
     * Получить по номеру договора
     * @return $this
     * @throws \Exception
     */
    public function loadByContract(string $contract) : self
    {
        try {
            $rowRegData = (new MSSQLProvider())->getByContract($contract);
        } catch (\Throwable $e) {
            echo __CLASS__ . "|" . __FUNCTION__  . "|Ошибка импорта данных|{$e->getMessage()}\n";
            exit();
        }

        $regData = array_merge($this->regData, $this->assignRegData($rowRegData));

        $this->regData = $regData;

        return $this;
    }

    /**
     * Получить по UID договора
     * @return $this
     * @throws \Exception
     */
    public function loadByUID(string $UID) : self
    {
        try {
            $rowRegData = (new MSSQLProvider())->getByUID($UID);
        } catch (\Throwable $e) {
            echo __CLASS__ . "|" . __FUNCTION__  . "|Ошибка импорта данных|{$e->getMessage()}\n";
            exit();
        }

        $regData = array_merge($this->regData, $this->assignRegData($rowRegData));

        $this->regData = $regData;

        return $this;
    }

    /**
     * Исключить займ из действующего списка кандидатов на отправку по его номеру договора
     * @return $this
     * @throws \Exception
     */
    public function excludeContract(string $contract) : self
    {
        try {
            (new MSSQLProvider())->excludeContract($contract);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось исключить займ из списка кандидатов на отправку";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }

        return $this;
    }

    /**
     * Включить займ в действующий список кандидатов на отправку по его номеру договора
     * @return $this
     * @throws \Exception
     */
    public function includeContract(string $contract) : self
    {
        try {
            (new MSSQLProvider())->includeContract($contract);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось добавить займ в список кандидатов на отправку";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }

        return $this;
    }
}