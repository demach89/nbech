<?php

namespace Core\Provider;
use Core\Provider\MSSQL\Connector\DB;
use Core\Provider\MSSQL\Query\NBKIQuery;

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../../../libs/func.php";

/**
 * Поставщик данных - MSSQL
 * Class MSSQLProvider
 * @package Core\Provider
 */
class MSSQLProvider
{
    /** @var DB Cвязь с БД */
    protected DB $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    /**
     * Получить все
     * @return array
     */
    public function get() : array
    {
        $params = [];

        try {
            $query = NBKIQuery::get();
            $rows_assoc = $this->db->multiplyRowQuery($query, $params);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось получить все записи";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }

        return $rows_assoc;
    }

    /**
     * Получить с даты начала периода
     * @param string $date
     * @return array
     */
    public function getAfterDate(string $date) : array
    {
        $params = [dateToMSSQLFormat($date)];

        try {
            $query = NBKIQuery::getAfterDate();
            $rows_assoc = $this->db->multiplyRowQuery($query, $params);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось получить записи с даты начала периода";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }

        return $rows_assoc;
    }

    /**
     * Получить по номеру договора
     * @param string $contract
     * @return array
     */
    public function getByContract(string $contract) : array
    {
        $params = [$contract];

        try {
            $query = NBKIQuery::getByContract();
            $rows_assoc = $this->db->singleRowQuery($query, $params);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось получить записи по номеру договора";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }

        return $rows_assoc;
    }

    /**
     * Получить по UID договора
     * @param string $UID
     * @return array
     */
    public function getByUID(string $UID) : array
    {
        $params = [$UID];

        try {
            $query = NBKIQuery::getByUID();
            $rows_assoc = $this->db->singleRowQuery($query, $params);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось получить записи по UID";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }

        return $rows_assoc;
    }

    /**
     * Исключить займ из действующего списка кандидатов на отправку по его номеру договора
     * @param string $contract
     */
    public function excludeContract(string $contract) : void
    {
        $params = [$contract];

        try {
            $query = NBKIQuery::excludeContract();
            $this->db->updateQuery($query, $params);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось исключить займ из списка кандидатов на отправку";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }
    }

    /**
     * Включить займ в действующий список кандидатов на отправку по его номеру договора
     * @param string $contract
     */
    public function includeContract(string $contract) : void
    {
        $params = [$contract];

        try {
            $query = NBKIQuery::includeContract();
            $this->db->updateQuery($query, $params);
        } catch (\Throwable $e) {
            $message = static::class . "|" . __METHOD__ . "|" . "Не удалось добавить займ в список кандидатов на отправку";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }
    }
}