<?php

namespace Core\Provider\MSSQL\Connector;

class DB
{
    protected $conn;

    public function __construct()
    {
        try {
            $serverName = MSSQL_CRED['address'];
            $connectionInfo = [
                "Database"  => MSSQL_CRED['dbname'],
                "UID"       => MSSQL_CRED['UID'],
                "PWD"       => MSSQL_CRED['PWD'],
                "CharacterSet" => "UTF-8",
                "TrustServerCertificate" => true,
            ];

            $this->conn = sqlsrv_connect($serverName, $connectionInfo);

            if (!$this->conn) {
                throw new \Error("Соединение не удалось");
            }
        } catch (\Throwable $e) {
            $message = "DB|construct|Error";
            error_log("{$message}|{$e->getMessage()}");
            echo $message;
            exit();
        }
    }

    /**
     * @param string $query
     * @param array $params
     * @return resource|void
     */
    public function query(string $query, array $params=[])
    {
        $resource = sqlsrv_query($this->conn, $query, $params, array( "Scrollable" => 'static' ));

        if( $resource === false ) {
            $message = "DB|query|Запрос не удался";
            error_log("{$message}|" . sqlsrv_errors()[0][2]);
            echo $message;
            exit();
        }

        return $resource;
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function singleRowQuery(string $query, array $params=[]) : array
    {
        $stmt =  $this->query($query, $params);

        return $this->singleResultProvider($stmt);
    }

            /**
             * Возврат единичного результата запроса
             * @param  $stmt
             * @return array
             */
            protected function singleResultProvider($stmt) : array
            {
                try {
                    if (sqlsrv_num_rows($stmt) > 1) {
                        throw new \Error("Запрос вернул более 1 результата, что недопустимо");
                    }

                    $queryResult = $this->fetch($stmt);
                } catch (\Throwable $e) {
                    $message = "DB|singleResultProvider|Error";
                    error_log("{$message}|{$e->getMessage()}");
                    echo $message;
                    exit();
                }

                return $queryResult;
            }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function multiplyRowQuery(string $query, array $params=[]) : array
    {
        $stmt =  $this->query($query, $params);

        return $this->multipleResultProvider($stmt);
    }

            /**
             * Возврат множественного результата запроса
             * @param  $stmt
             * @return array
             */
            protected function multipleResultProvider($stmt) : array
            {
                try {
                    $queryResult = $this->fetch($stmt);
                } catch (\Throwable $e) {
                    $message = "DB|multipleResultProvider|Error";
                    error_log("{$message}|{$e->getMessage()}");
                    echo $message;
                    exit();
                }

                return $queryResult;
            }

    /**
     * Извлечь результат запроса
     * @param  $stmt
     * @return array
     */
    protected function fetch($stmt) : array {
        try {
            $queryResult = [];

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $queryResult[] = $row;
            }

        } catch (\Throwable $e) {
            throw new \Error("Проблема с извлечением результата|{$e->getMessage()}");
        }

        return $queryResult;
    }

    /**
     * @param string $query
     * @param array $params
     */
    public function updateQuery(string $query, array $params=[]) : void
    {
        $this->query($query, $params);
    }
}