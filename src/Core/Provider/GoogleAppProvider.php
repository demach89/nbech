<?php

namespace Core\Provider;
use Core\Portfolio\PortfolioServiceDefiner;

require_once __DIR__ . "/../env.php";

/**
 * Поставщик данных - GoogleApp
 * Class GoogleAppProvider
 * @package Core\Provider
 */
class GoogleAppProvider
{
    protected string $portfolioCode;
    protected string $periodStart;
    protected string $googleApp;
    protected array  $data;

    public function __construct(string $portfolioCode, string $periodStart)
    {
        $this->portfolioCode = $portfolioCode;
        $this->periodStart = $periodStart;

        $this->init();
    }

    /** Инициализация приложения */
    protected function init() : void
    {
        $this->googleApp = $this->defineGoogleApp();

        $this->portfolioCode = stripos($this->portfolioCode, 'ALL') ?
            'ALL' : $this->portfolioCode;

        try {
            $googleData = $this->getGoogleData();
        } catch (\Throwable $e) {
            echo $e->getMessage();
            exit(0);
        }

        if (!array_key_exists('result', $googleData)) {
            echo "No result data...\n";
            exit();
        }

        $this->data = $googleData['result'];
    }

    /**
     * Получить Google-данные
     * @return array
     * @throws \JsonException|\Exception
     */
    protected function getGoogleData() : array
    {
        try {
            $response = $this->googleRequest();
        } catch (\Throwable $e) {
            throw new \Exception(__CLASS__ . "|" . __FUNCTION__  . "|Ошибка получения данных|{$e->getMessage()}");
        }

        if (!$response) {
            throw new \Exception(__CLASS__ . "|" . __FUNCTION__  . "|Нет данных");
        }

        return $response;
    }

            /**
             * Запрос данных от Google
             * @return array
             * @throws \JsonException
             */
            protected function googleRequest() : array
            {
                $requestData = array(
                    'portfolioCode' => $this->portfolioCode,
                    'exportDate' => $this->periodStart,
                );

                $options = array(
                    'http' => array(
                        'method'  => 'POST',
                        'content' => json_encode($requestData),
                        'header'  =>  "Content-Type: application/json\r\n" .
                            "Accept: application/json\r\n"
                    )
                );

                $context  = stream_context_create( $options );
                $result = file_get_contents( $this->googleApp, false, $context );

                return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
            }

            /**
             * Запрос данных от Google - резервный метод
             * @return array
             * @throws \JsonException
             */
            protected function googleRequest_reserve()
            {
                $header  = [
                    'Content-Type: application/json',
                ];

                $requestData = array(
                    'portfolioCode' => $this->portfolioCode,
                    'exportDate' => $this->periodStart,
                );

                $ch = curl_init();
                $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/'.rand(8,100).'.0';
                curl_setopt($ch, CURLOPT_URL, $this->googleApp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, false);
                //curl_setopt($ch, CURLOPT_VERBOSE, 1);
                //curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));

                curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSLVERSION,CURL_SSLVERSION_DEFAULT);

                $webcontent= curl_exec($ch);

                curl_close ($ch);

                return  json_decode($webcontent, true);
            }

    /**
     * Определить обслуживающее портфель Google-приложение
     * @return string
     * @throws \Exception
     */
     protected function defineGoogleApp() : string
     {
         $service = (new PortfolioServiceDefiner($this->portfolioCode))->define();

         return match ($service) {
             'google'  => GOOGLE_WEB_APPS['google']
         };

     }

    /**
     * Проверить существование портфеля
     * TARGET - таргетная выборка (лист TARGET)
     */
    /*protected function validatePortfolioExists() : void
    {
        if ( !((array_key_exists($this->portfolioCode, NBKI_SERVICE_GOOGLE)) || $this->portfolioCode === 'TARGET') ) {
            exit();
        }
    }*/

    /** Вернуть сырые данные от GoogleAppProvider */
    public function getData() : array
    {
        return $this->data;
    }
}