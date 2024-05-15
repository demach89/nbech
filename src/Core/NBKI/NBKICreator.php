<?php

namespace Core\NBKI;
use Core\Assist\VarPrep;

require_once __DIR__ . '/nbkiFunc.php';

/**
 * Класс подготовки НБКИ-выгрузки
 * - сопоставляет поля реестра с параметрами НБКИ (RUTDF)
 * - запускает подготовку НБКИ-блоков из параметров НБКИ (NBKIConstructor)
 *
 * Class NBKIProcessor
 * @package Core\NBKI
 */
abstract class NBKICreator
{
    protected array  $regData;
    protected array  $nbkiData;
    protected array  $nbkiBlocks;
    protected string $filename;
    protected int    $regTimestamp;
    protected string $dateDMY;

    public function __construct(array $regData, int $regTimestamp)
    {
        $this->regTimestamp = $regTimestamp;
        $this->dateDMY      = date('d.m.Y', $regTimestamp);

        $this->regData    = $regData;
        $this->nbkiData   = $this->convertRegToNBKIData();
        $this->nbkiBlocks = [];

        $this->filename = NBKI_CUSTOMER_CODE . "_" . date('Ymd', $regTimestamp) . "_" . date('His', $regTimestamp);

        try {
            $this->makeNBKIBlocks();
        } catch (\Throwable $e) {
            echo "NBKIProcessor: {$e->getMessage()}\n";
            exit();
        }
    }

    /** Создать НБКИ-блоки из набора данных для НБКИ */
    protected function makeNBKIBlocks() : self
    {
        $NBKIConstructor = new NBKIConstructor($this->regTimestamp);

        $NBKIConstructor->addHeader($this->filename);

        foreach ($this->nbkiData as $nbkiDatum) {
            $NBKIConstructor->addContent($nbkiDatum);
        }

        $NBKIConstructor->addFooter(count($this->nbkiData));

        $this->nbkiBlocks = $NBKIConstructor->getNbkiBlocks();

        return $this;
    }

    /**
     * Получить блок '0_GROUPHEADER'
     * Заголовок группы блоков
     * @param array $regDatum
     * @return array
     */
    protected function get_0_GROUPHEADER(array $regDatum) : array
    {
        return [
            'eventNumber'   => $this->getEventNumber($regDatum['amtOutstandingAll']),                               // 2 // Номер события, вследствие которого сформирована данная группа блоков
            'operationCode' => 'B',                                                                                 // 3 // Код операции, в рамках которой сформирована группа блоков показателей (B – кредитная информация изменяется или дополняется)
            'comment'       => '{"comment": "ECO"}',                                                                // 4 // Коммент
            'date'          => $this->getGroupheaderDate($regDatum['amtOutstandingAll'], $regDatum['paymtDate']),   // 5 // Дата события, вследствие которого сформирована данная группа блоков (для закрытых - дата последнего платежа, иначе - текущая дата)
        ];
    }

            /**
             * Получить параметр eventNumber
             * Номер события, вследствие которого сформирована данная группа блоков (2.3 - для незакрытых, 2.5 - для закрытых)
             */
            protected function getEventNumber(float $amtOutstandingAll) : string
            {
                return match (isContractClosed($amtOutstandingAll)) {
                    true   => '2.5', // для закрытых
                    false  => '2.3'  // для незакрытых
                };
            }

            /**
             * Получить параметр date
             * Дата события, вследствие которого сформирована данная группа блоков (для закрытых - дата последнего платежа, иначе - текущая дата)
             */
            protected function getGroupheaderDate(float $amtOutstandingAll, string $paymtDate) : string
            {
                return match (isContractClosed($amtOutstandingAll)) {
                    true   => $paymtDate,       // для закрытых
                    false  => $this->dateDMY    // для незакрытых
                };
            }

    /**
     * Получить блок 'C1_NAME'
     * ФИО
     * @param array $regDatum
     * @return array
     */
    protected function get_C1_NAME(array $regDatum) : array
    {
        return [
            'lastname'  => $this->getLastname($regDatum['fio']),   // 1 // Фамилия КЛИЕНТА
            'firstname' => $this->getFirstname($regDatum['fio']),  // 2 // Имя КЛИЕНТА
            'surname'   => $this->getSurname($regDatum['fio']),    // 3 // Отчество КЛИЕНТА
        ];
    }

            /** Получить фамилию клиента */
            protected function getLastname(string $fio) : string
            {
                return explode(' ', $fio)[0];
            }

            /** Получить имя клиента */
            protected function getFirstname(string $fio) : string
            {
                return explode(' ', $fio)[1];
            }

            /** Получить отчество клиента */
            protected function getSurname(string $fio) : string
            {
                $fioParts = explode(' ', $fio);

                $surname = implode(
                    ' ',
                    array_slice($fioParts, 2)
                );

                return $surname;
            }


    /**
     * Получить блок 'C2_PREVNAME'
     * ФИО предыдущее
     * @param array $regDatum
     * @return array
     */
    protected function get_C2_PREVNAME(array $regDatum) : array
    {
        return [
            'isPrevName'=> 0,   // 1 // Факт смены ФИО
            'lastname'  => '',  // 2 // Прежняя фамилия КЛИЕНТА
            'firstname' => '',  // 3 // Прежнее имя КЛИЕНТА
            'surname'   => '',  // 4 // Прежнее отчество КЛИЕНТА
            'issueDate' => '',  // 5 // Дата выдачи документа с измененным именем
        ];
    }

    /**
     * Получить блок 'C3_BIRTH'
     * Дата и место рождения
     * @param array $regDatum
     * @return array
     */
    protected function get_C3_BIRTH(array $regDatum) : array
    {
        return [
            'birthDt'       => $regDatum['birthDt'],        // 1 // Дата рождения клиента
            'OKSM'          => 643,                         // 2 // Цифровой код страны согласно Общероссийскому классификатору стран мира (ОКСМ)
            'placeOfBirth'  => $regDatum['placeOfBirth'],   // 3 // Место рождения клиента
        ];
    }

    /**
     * Получить блок 'C4_ID'
     * Документ, удостоверяющий личность
     * @param array $regDatum
     * @return array
     */
    protected function get_C4_ID(array $regDatum) : array
    {
        return [
            'OKSM'              => 643,                                             // 1  // Цифровой код страны согласно Общероссийскому классификатору стран мира (ОКСМ)
            'otherCountry'      => '',                                              // 2  // Заполняется, если по показателю «Код страны по ОКСМ» указано «999»
            'idType'            => 21,                                              // 3  // Код документа (21 = паспорт)
            'otherId'           => '',                                              // 4  // Наименование иного документа
            'seriesNumber'      => $this->getSeriesNumber($regDatum['seriesNum']),  // 5  // Серия паспорта
            'idNum'             => $this->getIdNum($regDatum['seriesNum']),         // 6  // Номер паспорта
            'issueDate'         => $regDatum['issueDate'],                          // 7  // Дата выдачи паспорта
            'issueAuthority'    => $regDatum['issueAuthority'],                     // 8  // Кем выдан паспорт
            'divCode'           => $regDatum['divCode'],                            // 9  // Код подразделения
            'validTo'           => '',                                              // 10 // Дата окончания срока действия документа
        ];
    }

            /**
             * Получить параметр seriesNumber
             * Паспорт - серия
             * @param string $seriesNum
             * @return string
             */
            protected function getSeriesNumber(string $seriesNum) : string
            {
                return mb_substr($seriesNum, 0, 4);
            }

            /**
             * Получить параметр idNum
             * Паспорт - номер
             * @param string $seriesNum
             * @return string
             */
            protected function getIdNum(string $seriesNum) : string
            {
                return mb_substr($seriesNum, 4);
            }

    /**
     * Получить блок 'C5_PREVID'
     * Документ, ранее удостоверявший личность
     * @param array $regDatum
     * @return array
     */
    protected function get_C5_PREVID(array $regDatum) : array
    {
        return [
            'isPrevId'          => 0,   // 1  // Документ, ранее удостоверявший личность (0 - нет, 1 - да). При 0 остальные пустые
            'OKSM'              => '',  // 2  // Цифровой код страны согласно Общероссийскому классификатору стран мира (ОКСМ)
            'otherCountry'      => '',  // 3  // Заполняется, если по показателю «Код страны по ОКСМ» указано «999»
            'idType'            => '',  // 4  // Код документа (21 = паспорт)
            'otherId'           => '',  // 5  // Наименование иного документа
            'seriesNumber'      => '',  // 6  // Серия паспорта
            'idNum'             => '',  // 7  // Серия паспорта
            'issueDate'         => '',  // 8  // Дата выдачи паспорта
            'issueAuthority'    => '',  // 9  // Кем выдан паспорт
            'divCode'           => '',  // 10 // Код подразделения
            'validTo'           => '',  // 11 // Дата окончания срока действия документа
        ];
    }

    /**
     * Получить блок 'C6_REGNUM'
     * ИНН и налоговый режим (блок-константа)
     * @param array $regDatum
     * @return array
     */
    protected function get_C6_REGNUM(array $regDatum) : array
    {
        return [
            'taxpayerCode'  => '',  // 1 // Код номера налогоплательщика (константа)
            'taxpayerNum'   => '',  // 2 // Номер налогоплательщика (константа)
            'regNum'        => '',  // 3 // Регистрационный номер (константа)
            'spectaxCode'   => 0,   // 4 // Признак специального налогового режима (константа)

        ];
    }

    /**
     * Получить блок 'C7_SNILS'
     * СНИЛС
     * @param array $regDatum
     * @return array
     */
    protected function get_C7_SNILS(array $regDatum) : array
    {
        return [
            'SNILS'  => $regDatum['SNILS'], // 1 // СНИЛС
        ];
    }

    /**
     * Получить блок 'C17_UID'
     * УИд сделки
     * @param array $regDatum
     * @return array
     */
    protected function get_C17_UID(array $regDatum) : array
    {
        return [
            'uuid'      => $regDatum['uuid'],   // 1 // UID
            'acctNum'   => $regDatum['acctNum'],// 2 // Номер сделки
        ];
    }

    /**
     * Получить блок 'C18_TRADE'
     * Общие сведения о сделке
     * @param array $regDatum
     * @return array
     */
    protected function get_C18_TRADE(array $regDatum) : array
    {
        return [
            'ownerIndic'                => 1,                                      // 1  // Код участника (1 - заёмщик)
            'openedDt'                  => $regDatum['openedDt'],                  // 2  // ДАТА ВЫДАЧИ ЗАЙМА
            'tradeTypeCode'             => 1,                                      // 3  // Тип сделки (1 - договор займа)
            'loanKindCode'              => 13,                                     // 4  // Тип займа (13 - Необеспеченный микрозаем)
            'acctType'                  => 99,                                     // 5  // Цель кредита (99 - иное)
            'isConsumerLoan'            => 1,                                      // 6  // Кредит
            'hasCard'                   => $this->getHasCard($regDatum['hasCard']),// 7  // ТИП ВЫДАЧИ ЗАЙМА
            'isNovation'                => $regDatum['isNovation'],                // 8  // НОВАЦИЯ ФАКТ
            'isMoneySource'             => 1,                                      // 9  // Предмет=деньги
            'isMoneyBorrower'           => 1,                                      // 10 // Предмет=деньги
            'closeDt'                   => $regDatum['closeDt'],                   // 11 // ДАТА ОКОНЧАНИЯ ЗАЙМА
            'lendertypeCode'            => 2,                                      // 12 // Лицо, осуществляющее деятельность по возврату просроченной задолженности (2 = Заимодавец – микрофинансовая организация)
            'obtainpartCred'            => 1,                                      // 13 // Признак возникновения обязательства в результате получения части прав кредитора от другого лица
            'creditLine'                => 0,                                      // 14 // Признак кредитной линии
            'creditLineCode'            => '',                                     // 15 // Код типа кредитной линии
            'interestrateFloat'         => 0,                                      // 16 // Признак плавающей (переменной) процентной ставки
            'transpartCred'             => 0,                                      // 17 // Признак частичной передачи прав кредитора другому лицу. Указывается источником, осуществившим частичную передачу прав кредитора по обязательству другому источнику
            'transpartCredUuid'         => $regDatum['uuid'],                      // 18 // УИд сделки, по которой права кредитора частично переданы другому лицу. Заполняется источником, к которому частично перешли права требования по обязательству субъекта
            'commitDate'                => $regDatum['openedDt'],                  // 19 // Дата возникновения обязательства субъекта. Указывается дата возникновения у субъекта обязательства в силу закона или по соглашению сторон
        ];
    }

            /**
             * Получить параметр hasCard
             * @param string $hasCard
             * @return string
             */
            protected function getHasCard(string $hasCard) : string
            {
                return match ($hasCard) {
                    'Онлайн', 'РНКО' => 1,
                    default => 0
                };
            }

    /**
     * Получить блок 'C19_ACCOUNTAMT'
     * Сумма и валюта обязательства
     * @param array $regDatum
     * @return array
     */
    protected function get_C19_ACCOUNTAMT(array $regDatum) : array
    {
        return [
            'creditLimit'               => VarPrep::nbkiDgt($regDatum['creditLimit']),  // 1 // СУММА ВЫДАЧИ
            'currencyCode'              => 'RUB',                                  // 2 // Валюта обязательства
            'ensuredAmt'                => '',                                     // 3 // Сумма обеспечиваемого обязательства
            'commitcurrCode'            => '',                                     // 4 // Валюта обеспечиваемого обязательства
            'commitCode'                => '',                                     // 5 // Код типа обеспечиваемого обязательства
            'amtDate'                   => $regDatum['openedDt'],                  // 6 // Дата расчета
            'commitUuid'                => '',                                     // 7 // УИд сделки, в результате которой возникло обеспечиваемое обязательство
        ];
    }

    /**
     * Получить блок 'C21_PAYMTCONDITION'
     * Сведения об условиях платежей, без учёта просрочки
     * @param array $regDatum
     * @return array
     */
    protected function get_C21_PAYMTCONDITION(array $regDatum) : array
    {
        return match (isCloseDatePassed($regDatum['closeDt'], $this->regTimestamp)) {
            true  => [
                'principalTermsAmt'         => '0,00',  // 1 // Сумма ближайшего следующего платежа по основному долгу, без учёта просрочки
                'principalTermsAmtDt'       => '',      // 2 // Дата ближайшего следующего платежа по основному долгу
                'interestTermsAmt'          => '0,00',  // 3 // Сумма ближайшего следующего платежа по процентам, без учёта просрочки
                'interestTermsAmtDt'        => '',      // 4 // Дата ближайшего следующего платежа по процентам
                'termsFrequency'            => '',      // 5 // Код частоты платежей (99 - иное)
                'minPaymt'                  => '',      // 6 // Сумма минимального платежа по кредитной карте
                'graceStartDt'              => '',      // 7 // Дата начала беспроцентного периода
                'graceEndDt'                => '',      // 8 // Дата окончания беспроцентного периода
                'interestPaymentDueDate'    => '',      // 9 // Дата окончания срока уплаты процентов
            ],
            false => [
                'principalTermsAmt'         => VarPrep::nbkiDgt($regDatum['principalOutstanding']),  // 1 // Сумма ближайшего следующего платежа по основному долгу, без учёта просрочки // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНОМУ ОСНОВНОМУ ДОЛГУ
                'principalTermsAmtDt'       => $regDatum['closeDt'],                            // 2 // Дата ближайшего следующего платежа по основному долгу
                'interestTermsAmt'          => VarPrep::nbkiDgt($regDatum['intOutstanding']),        // 3 // Сумма ближайшего следующего платежа по процентам, без учёта просрочки // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНЫМ ПРОЦЕНТАМ
                'interestTermsAmtDt'        => $regDatum['closeDt'],                            // 4 // Дата ближайшего следующего платежа по процентам
                'termsFrequency'            => 99,                                              // 5 // Код частоты платежей (99 - иное)
                'minPaymt'                  => '',                                              // 6 // Сумма минимального платежа по кредитной карте
                'graceStartDt'              => '',                                              // 7 // Дата начала беспроцентного периода
                'graceEndDt'                => '',                                              // 8 // Дата окончания беспроцентного периода
                'interestPaymentDueDate'    => $regDatum['closeDt'],                            // 9 // Дата окончания срока уплаты процентов
            ],
        };
    }

    /**
     * Получить блок 'C22_OVERALLVAL'
     * Полная стоимость потребительского кредита (займа)
     * @param array $regDatum
     * @return array
     */
    protected function get_C22_OVERALLVAL(array $regDatum) : array
    {
        return [
            'creditTotalAmt'            => VarPrep::nbkiDgt($regDatum['creditTotalAmt'],3), // 1 // Полная стоимость кредита (займа) в процентах годовых
            'creditTotalMonetaryAmt'    => VarPrep::nbkiDgt($regDatum['creditTotalMonetaryAmt']),   // 2 // Полная стоимость кредита (займа) в денежном выражении
            'creditTotalAmtDate'        => $regDatum['openedDt'],                                   // 3 // Дата расчета полной стоимости кредита (займа)
        ];
    }

    /**
     * Получить блок 'C24_FUNDDATE'
     * Дата передачи финансирования субъекту или возникновения обеспечения исполнения обязательства
     * @param array $regDatum
     * @return array
     */
    protected function get_C24_FUNDDATE(array $regDatum) : array
    {
        return [
            'fundDate'  => $regDatum['openedDt'],  // 1 // Дата выдачи
            'trancheNum'  => '',                   // 2 // Порядковый номер транша. Заполняется для займа (кредита), который выдается траншами
        ];
    }

    /**
     * Получить блок 'C25_ARREAR'
     * Сведения о задолженности
     * @param array $regDatum
     * @return array
     */
    protected function get_C25_ARREAR(array $regDatum) : array
    {
        return match (!isContractClosed($regDatum['amtOutstandingAll'])) {
            true  => [
                'isArrearExists'            => 1,                                                   // 1 // Признак наличия задолженности
                'startAmtOutstanding'       => VarPrep::nbkiDgt($regDatum['creditLimit']),               // 2 // Сумма задолженности на дату передачи финансирования субъекту
                'lastPaymentDueCode'        => $regDatum['lastPaymentDueCode'],                          // 3 // Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе)
                'amtOutstanding'            => VarPrep::nbkiDgt($regDatum['amtOutstandingAll']),         // 4 // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ
                'principalOutstanding'      => VarPrep::nbkiDgt($regDatum['principalOutstandingAll']),   // 5 // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ОСНОВНОМУ ДОЛГУ
                'intOutstanding'            => VarPrep::nbkiDgt($regDatum['intOutstandingAll']),         // 6 // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ПРОЦЕНТАМ
                'otherAmtOutstanding'       => VarPrep::nbkiDgt($regDatum['otherAmtPastDue']),           // 7 // Сумма задолженности по иным требованиям
                'calcDate'                  => $regDatum['calcDate']?? $this->dateDMY,                   // 8 // Дата расчета
                'unconfirmGrace'            => 0,                                                   // 9 // Признак неподтвержденного льготного периода (0)
            ],
            default => [
                'isArrearExists'            => 0,   // 1 // Признак наличия задолженности
                'startAmtOutstanding'       => '',  // 2 // Сумма задолженности на дату передачи финансирования субъекту
                'lastPaymentDueCode'        => '',  // 3 // Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе)
                'amtOutstanding'            => '',  // 4 // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ
                'principalOutstanding'      => '',  // 5 // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ОСНОВНОМУ ДОЛГУ
                'intOutstanding'            => '',  // 6 // ОБЩАЯ СУММА ЗАДОЛЖЕННОСТИ ПО ПРОЦЕНТАМ
                'otherAmtOutstanding'       => '',  // 7 // Сумма задолженности по иным требованиям
                'calcDate'                  => '',  // 8 // Дата расчета
                'unconfirmGrace'            => '',  // 9 // Признак неподтвержденного льготного периода (0)
            ],
        };
    }

    /**
     * Получить блок 'C26_DUEARREAR'
     * Сведения о срочной задолженности
     * @param array $regDatum
     * @return array
     */
    protected function get_C26_DUEARREAR(array $regDatum) : array
    {
        return match (isCloseDatePassed($regDatum['closeDt'], $this->regTimestamp)) {
            true  => [
                'startDt'               => '',                      // 1 // Дата возникновения срочной задолженности
                'lastPaymentDueCode'    => '',                      // 2 // Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе)
                'amtOutstanding'        => '0,00',                  // 3 // ОБЩАЯ СУММА СРОЧНОЙ ЗАДОЛЖЕННОСТИ
                'principalOutstanding'  => '',                      // 4 // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНОМУ ОСНОВНОМУ ДОЛГУ
                'intOutstanding'        => '',                      // 5 // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНЫМ ПРОЦЕНТАМ
                'otherAmtOutstanding'   => '',                      // 6 // Сумма срочной задолженности по иным требованиям
                'calcDate'              => '',                      // 7 // Дата расчета
            ],
            false => [
                'startDt'               => $regDatum['openedDt'],                                // 1 // Дата возникновения срочной задолженности
                'lastPaymentDueCode'    => $regDatum['lastPaymentDueCode'],                      // 2 // Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе)
                'amtOutstanding'        => VarPrep::nbkiDgt($regDatum['amtOutstanding']),        // 3 // ОБЩАЯ СУММА СРОЧНОЙ ЗАДОЛЖЕННОСТИ
                'principalOutstanding'  => VarPrep::nbkiDgt($regDatum['principalOutstanding']),  // 4 // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНОМУ ОСНОВНОМУ ДОЛГУ
                'intOutstanding'        => VarPrep::nbkiDgt($regDatum['intOutstanding']),        // 5 // СУММА ЗАДОЛЖЕННОСТИ ПО СРОЧНЫМ ПРОЦЕНТАМ
                'otherAmtOutstanding'   => '0,00',                                               // 6 // Сумма срочной задолженности по иным требованиям
                'calcDate'              => $regDatum['calcDate']?? $this->dateDMY,               // 7 // Дата расчета
            ],
        };
    }

    /**
     * Получить блок 'C27_PASTDUEARREAR'
     * Сведения о просроченной задолженности
     * @param array $regDatum
     * @return array
     */
    protected function get_C27_PASTDUEARREAR(array $regDatum) : array
    {
        return match ($regDatum['amtPastDue'] > 0) {
            true  => [
                'pastDueDt'             => $regDatum['pastDueDt'],                               // 1 // Дата возникновения просроченной задолженности
                'lastPaymentDueCode'    => $regDatum['lastPaymentDueCode'],                      // 2 // Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе)
                'amtPastDue'            => VarPrep::nbkiDgt($regDatum['amtPastDue']),            // 3 // Сумма просроченной задолженности
                'principalAmtPastDue'   => VarPrep::nbkiDgt($regDatum['principalAmtPastDue']),   // 4 // Сумма просроченной задолженности по основному долгу
                'intAmtPastDue'         => VarPrep::nbkiDgt($regDatum['intAmtPastDue']),         // 5 // Сумма просроченной задолженности по процентам
                'otherAmtPastDue'       => VarPrep::nbkiDgt($regDatum['otherAmtPastDue']),       // 6 // Сумма просроченной задолженности по иным требованиям
                'calcDate'              => $regDatum['calcDate']?? $this->dateDMY,               // 7 // Дата расчёта
                'principalMissedDate'   => $regDatum['pastDueDt'],                          // 8 // Дата последнего пропущенного платежа по основному долгу
                'intMissedDate'         => $regDatum['pastDueDt'],                          // 9 // Дата последнего пропущенного платежа по процентам
            ],
            false => [
                'pastDueDt'             => $regDatum['pastDueDt'],  // 1 // Дата возникновения просроченной задолженности
                'lastPaymentDueCode'    => '',                      // 2 // Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе)
                'amtPastDue'            => '0,00',                  // 3 // Сумма просроченной задолженности
                'principalAmtPastDue'   => '',                      // 4 // Сумма просроченной задолженности по основному долгу
                'intAmtPastDue'         => '',                      // 5 // Сумма просроченной задолженности по процентам
                'otherAmtPastDue'       => '',                      // 6 // Сумма просроченной задолженности по иным требованиям
                'calcDate'              => $regDatum['calcDate']?? $this->dateDMY, // 7 // Дата расчёта
                'principalMissedDate'   => '',                      // 8 // Дата последнего пропущенного платежа по основному долгу
                'intMissedDate'         => '',                      // 9 // Дата последнего пропущенного платежа по процентам
            ],
        };
    }

    /**
     * Получить блок 'C28_PAYMT'
     * Сведения о внесении платежей
     * @param array $regDatum
     * @return array
     */
    protected function get_C28_PAYMT(array $regDatum) : array
    {
        return match ($regDatum['paymtAmt'] > 0) {
            true => [
                'paymtDate'         => $regDatum['paymtDate'],                                      // 1  // Дата последнего внесенного платежа
                'paymtAmt'          => VarPrep::nbkiDgt($regDatum['paymtAmt']),                     // 2  // Сумма последнего внесенного платежа
                'principalPaymtAmt' => VarPrep::nbkiDgt($regDatum['principalPaymtAmt']),            // 3  // Сумма последнего внесенного платежа по основному долгу
                'intPaymtAmt'       => VarPrep::nbkiDgt($regDatum['intPaymtAmt']),                  // 4  // Сумма последнего внесенного платежа по процентам
                'otherPaymtAmt'     => VarPrep::nbkiDgt($regDatum['otherPaymtAmt']),                // 5  // Сумма последнего внесенного платежа по иным требованиям
                'totalAmt'          => VarPrep::nbkiDgt($regDatum['totalAmt']),                     // 6  // Сумма всех внесенных платежей по обязательству
                'principalTotalAmt' => VarPrep::nbkiDgt($regDatum['principalTotalAmt']),            // 7  // Сумма внесенных платежей по основному долгу
                'intTotalAmt'       => VarPrep::nbkiDgt($regDatum['intTotalAmt']),                  // 8  // Сумма внесенных платежей по процентам
                'otherTotalAmt'     => VarPrep::nbkiDgt($regDatum['otherTotalAmt']),                // 9  // Сумма внесенных платежей по иным требованиям
                'amtKeepCode'       => $this->getAmtKeepCode($regDatum),                            // 10 // Код соблюдения размера платежей (1 - Платеж внесен в полном размере, 2 - Платеж внесен не в полном размере, 3 - Платеж не внесен)
                'termsDueCode'      => ($regDatum['daysPastDue'] > 0)? 3 : 2,                       // 11 // Код соблюдения срока внесения платежей
                'daysPastDue'       => $this->getDaysPastDue($regDatum),                            // 12 // Продолжительность просрочки (равен нулю при закрытии займа (правка от 2023-10-04)
            ],
            false => [
                'paymtDate'         => $regDatum['paymtDate'],                                      // 1  // Дата последнего внесенного платежа
                'paymtAmt'          => VarPrep::nbkiDgt($regDatum['paymtAmt']),                     // 2  // Сумма последнего внесенного платежа
                'principalPaymtAmt' => '',                                                          // 3  // Сумма последнего внесенного платежа по основному долгу
                'intPaymtAmt'       => '',                                                          // 4  // Сумма последнего внесенного платежа по процентам
                'otherPaymtAmt'     => '',                                                          // 5  // Сумма последнего внесенного платежа по иным требованиям
                'totalAmt'          => '',                                                          // 6  // Сумма всех внесенных платежей по обязательству
                'principalTotalAmt' => '',                                                          // 7  // Сумма внесенных платежей по основному долгу
                'intTotalAmt'       => '',                                                          // 8  // Сумма внесенных платежей по процентам
                'otherTotalAmt'     => '',                                                          // 9  // Сумма внесенных платежей по иным требованиям
                'amtKeepCode'       => $this->getAmtKeepCode($regDatum),                            // 10 // Код соблюдения размера платежей (1 - Платеж внесен в полном размере, 2 - Платеж внесен не в полном размере, 3 - Платеж не внесен)
                'termsDueCode'      => ($regDatum['daysPastDue'] > 0)? 3 : 2,                       // 11 // Код соблюдения срока внесения платежей
                'daysPastDue'       => $this->getDaysPastDue($regDatum),                            // 12 // Продолжительность просрочки (равен нулю при закрытии займа (правка от 2023-10-04)
            ],
        };

    }

            /**
             * Получить параметр amtKeepCode
             * Код соблюдения размера платежей (1 - Платеж внесен в полном размере, 2 - Платеж внесен не в полном размере, 3 - Платеж не внесен)
             * @param array $regDatum
             * @return int
             */
            protected function getAmtKeepCode(array $regDatum) : int
            {
                $AT_ALL_PAYMENT     = 1;
                $NOT_AT_ALL_PAYMENT = 2;
                $NO_PAYMENT         = 3;

                if (isContractClosed($regDatum['amtOutstandingAll'])) {
                    return $AT_ALL_PAYMENT;
                }

                if ($regDatum['totalAmt'] > 0) {
                    return $NOT_AT_ALL_PAYMENT;
                }

                return $NO_PAYMENT;
            }

            /**
             * Получить параметр daysPastDue
             * Равен нулю при закрытии займа (правка от 2023-10-04)
             * @param array $regDatum
             * @return int
             */
            protected function getDaysPastDue(array $regDatum) : int
            {
                return match (isContractClosed($regDatum['amtOutstandingAll'])) {
                    true  => 0,
                    false => $regDatum['daysPastDue']
                };
            }

    /**
     * Получить блок 'C29_MONTHAVERPAYMT'
     * Величина среднемесячного платежа
     * @param array $regDatum
     * @return array
     */
    protected function get_C29_MONTHAVERPAYMT(array $regDatum) : array
    {
        return [
            'averPaymtAmt'  => $this->getAverPaymtAmt($regDatum),       // 1 // Величина среднемесячного платежа
            'calcDate'      => $this->getAverPaymtCalcDt($regDatum),    // 2 // Дата расчета величины среднемесячного платежа
        ];
    }

            /**
             * Получить параметр averPaymtAmt (среднемесячный платеж, округляется до целого — НБКИ не принимает дроби в данном случае)
             * Если займ истёк:
             *    = СрочнаяЗадолженность/МесяцевДоЗакрытия + Просроченная задолженность
             * Иначе:
             *    = Просроченная задолженность
             * @param array $regDatum
             * @return int
             */
            protected function getAverPaymtAmt(array $regDatum) : int
            {
                $monthsToClose = monthsToClose($regDatum['closeDt'], $this->regTimestamp);

                if ($monthsToClose > 0) {
                    $averPaymtAmt = ($regDatum['amtOutstanding'] / $monthsToClose) + $regDatum['amtPastDue'];
                } else {
                    $averPaymtAmt = $regDatum['amtPastDue'];
                }

                return ROUND($averPaymtAmt);
            }

            /**
             * Получить параметр calcDate (дата расчёта среднемесячного платежа)
             * @param array $regDatum
             * @return mixed|string
             */
            protected function getAverPaymtCalcDt(array $regDatum)
            {
                return match (isContractClosed($regDatum['amtOutstandingAll'])){
                    true  => $regDatum['paymtDate'],
                    false => $regDatum['calcDate']?? $this->dateDMY,
                };
            }

    /**
     * Получить блок 'C38_OBLIGTERMINATION'
     * Сведения о прекращении обязательства (заполняется только при закрытии)
     * @param array $regDatum
     * @return array
     */
    protected function get_C38_OBLIGTERMINATION(array $regDatum) : array
    {
        return match (isContractClosed($regDatum['amtOutstandingAll'])) {
            true => [
                'loanIndicator'     => 1,                       // 1 // Код основания прекращения обязательства (1 - Надлежащее исполнение обязательства)
                'loanIndicatorDt'   => $regDatum['paymtDate'],  // 2 // Дата фактического прекращения обязательства // Дата последнего платежа
            ],
            false  => [],
        };
    }

    /**
     * Получить блок 'C54_OBLIGACCOUNT'
     * Сведения об учете обязательства
     * @param array $regDatum
     * @return array
     */
    protected function get_C54_OBLIGACCOUNT(array $regDatum) : array
    {
        return [
                'obligAccountCode'  => 1,                        // 1 // Обязательство учтено у источника на балансовых счетах
                'intRate'  => VarPrep::nbkiDgt($regDatum['intRate']), // 2 // Процентная ставка
                'offbalanceAmt'  => '',                          // 3 // Сумма обязательства, учтенная на внебалансовых счетах
                'preferenFinanc'  => 0,                          // 4 // Признак льготного финансирования с государственной поддержкой
                'preferenFinancInfo'  => '',                     // 5 // Информация о программе государственной поддержки
            ];
    }

    /**
     * Получить блок 'C56_OBLIGPARTTAKE'
     * Сведения об участии в обязательстве, по которому формируется КИ
     * @param array $regDatum
     * @return array
     */
    protected function get_C56_OBLIGPARTTAKE(array $regDatum) : array
    {
        return [
                'flagIndicatorCode'     => 1,                                                           // 1 // Код вида участия в сделке (1 - Заемщик)
                'approvedLoanTypeCode'  => 13,                                                          // 2 // Код вида займа (13 - Необеспеченный микрозаем)
                'agreementNumber'       => $regDatum['uuid'],                                           // 3 // УИД
                'fundDt'                => $regDatum['openedDt'],                                       // 4 // Дата передачи финансирования субъекту
                'defaultFlag'           => ($this->getDaysPastDue($regDatum) >= 90)? 1 : 0,             // 5 // Признак просрочки должника более 90 дней (0 - нет, 1 - да) (равен нулю при закрытии займа (правка от 2023-10-04))
                'loanIndicator'         => (isContractClosed($regDatum['amtOutstandingAll']))? 1 : 0,   // 6 // Признак прекращения обязательства (0 - займ действующий, 1 - займ закрыт)
            ];
    }

    /**
     * Получить блок 'DELETE'
     * Для удаления заявки
     * @return array
     */
    protected function get_DELETE() : array
    {
        return [];
    }

    /** Получить результирующий набор НБКИ-блоков для выгрузки */
    public function getNBKIBlocks() : array
    {
        return $this->nbkiBlocks;
    }

    /** Получить имя файла выгрузки */
    public function getFilename() : string
    {
        return $this->filename;
    }
}