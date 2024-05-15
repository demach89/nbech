<?php

namespace Core\NBKI;

/**
 * Класс сборки НБКИ-блоков
 * НБКИ-блоки - массивы данных для последующей записи в файл для в НБКИ
 *
 * Class NBKIConstructor
 * @package Core\NBKI
 */
class NBKIConstructor
{
    protected array  $nbkiData;
    protected array  $nbkiBlocks = [];

    protected string $date;
    protected int    $blockCounter = 0;

    public function __construct(int $timestamp)
    {
        $this->date = date("d.m.Y", $timestamp);
    }

    /**
     * Добавить заголовок в общий массив НБКИ-блоков
     * @param string $filename
     * @return $this
     */
    public function addHeader(string $filename) : self
    {
        $this->nbkiBlocks[] = $this->add_HEADER($filename);

        return $this;
    }

    /**
     * Добавить НБКИ-блок из набора данных НБКИ в общий массив НБКИ-блоков
     * @param array $nbkiData
     * @return $this
     */
    public function addContent(array $nbkiData) : self
    {
        $this->blockCounter++;

        $this->nbkiData = $nbkiData;

        $this->nbkiBlocks[] = $this->add_0_GROUPHEADER();
        $this->nbkiBlocks[] = $this->add_C1_NAME();
        $this->nbkiBlocks[] = $this->add_C2_PREVNAME();
        $this->nbkiBlocks[] = $this->add_C3_BIRTH();
        $this->nbkiBlocks[] = $this->add_C4_ID();
        $this->nbkiBlocks[] = $this->add_C5_PREVID();
        $this->nbkiBlocks[] = $this->add_C6_REGNUM();
        $this->nbkiBlocks[] = $this->add_C7_SNILS();
        $this->nbkiBlocks[] = $this->add_C17_UID();
        $this->nbkiBlocks[] = $this->add_C18_TRADE();
        $this->nbkiBlocks[] = $this->add_C19_ACCOUNTAMT();
        $this->nbkiBlocks[] = $this->add_C21_PAYMTCONDITION();
        $this->nbkiBlocks[] = $this->add_C22_OVERALLVAL();
        $this->nbkiBlocks[] = $this->add_C24_FUNDDATE();
        $this->nbkiBlocks[] = $this->add_C25_ARREAR();
        $this->nbkiBlocks[] = $this->add_C26_DUEARREAR();
        $this->nbkiBlocks[] = $this->add_C27_PASTDUEARREAR();
        $this->nbkiBlocks[] = $this->add_C28_PAYMT();
        $this->nbkiBlocks[] = $this->add_C29_MONTHAVERPAYMT();

        if (!empty($this->nbkiData['C38_OBLIGTERMINATION'])) {
            $this->nbkiBlocks[] = $this->add_C38_OBLIGTERMINATION();
        }

        if (!empty($this->nbkiData['C54_OBLIGACCOUNT'])) {
            $this->nbkiBlocks[] = $this->add_C54_OBLIGACCOUNT();
        }

        $this->nbkiBlocks[] = $this->add_C56_OBLIGPARTTAKE();

        return $this;
    }

    /**
     * Добавить НБКИ-блок из набора данных НБКИ в общий массив НБКИ-блоков (для удаления данных из НБКИ)
     * @param array $nbkiData
     * @return $this
     */
    public function addContentToDelete(array $nbkiData) : self
    {
        $this->blockCounter++;

        $this->nbkiData = $nbkiData;

        $this->nbkiBlocks[] = $this->add_0_GROUPHEADER();
        $this->nbkiBlocks[] = $this->add_C1_NAME();
        $this->nbkiBlocks[] = $this->add_C2_PREVNAME();
        $this->nbkiBlocks[] = $this->add_C3_BIRTH();
        $this->nbkiBlocks[] = $this->add_C4_ID();
        $this->nbkiBlocks[] = $this->add_C5_PREVID();
        $this->nbkiBlocks[] = $this->add_C7_SNILS();
        $this->nbkiBlocks[] = $this->add_DELETE();
        $this->nbkiBlocks[] = $this->add_C17_UID();
        $this->nbkiBlocks[] = $this->add_C18_TRADE();
        $this->nbkiBlocks[] = $this->add_C19_ACCOUNTAMT();

        return $this;
    }

    /**
     * Добавить подвал в общий массив НБКИ-блоков
     * @param int $cliensCount
     * @return $this
     */
    public function addFooter(int $cliensCount) : self
    {
        $this->nbkiBlocks[] = $this->add_FOOTER($cliensCount);

        return $this;
    }


    /** ************************************************************************** */


    /** Содержимое поля 'HEADER' (RUTDF) */
    protected function add_HEADER(string $filename) : array
    {
        return [
            0  => 'HEADER',
            1  => NBKI_CUSTOMER_INN,
            2  => NBKI_CUSTOMER_ORGN,
            3  => $filename,
            4  => $this->date,
            5  => '',
            6  => NBKI_CUSTOMER_CODE,
            7  => NBKI_CUSTOMER_PWD,
            8  => NBKI_RUTDF_VER,
            9  => '',
            10 => $this->date,
            11 => '',
            12 => '',
        ];
    }

    /** Содержимое поля '0_GROUPHEADER' (RUTDF) */
    protected function add_0_GROUPHEADER() : array
    {
        $blockName = '0_GROUPHEADER';

        return [
            0 => $blockName,
            1 => $this->blockCounter,
            2 => $this->nbkiData[$blockName]['eventNumber'],
            3 => $this->nbkiData[$blockName]['operationCode'],
            4 => $this->nbkiData[$blockName]['comment'],
            5 => $this->nbkiData[$blockName]['date'],
        ];
    }

    /** Содержимое поля 'C1_NAME' (RUTDF) */
    protected function add_C1_NAME() : array
    {
        $blockName = 'C1_NAME';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['lastname'],
            2 => $this->nbkiData[$blockName]['firstname'],
            3 => $this->nbkiData[$blockName]['surname'],
        ];
    }

    /** Содержимое поля 'C2_PREVNAME' (RUTDF) */
    protected function add_C2_PREVNAME() : array
    {
        $blockName = 'C2_PREVNAME';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['isPrevName'],
            2 => $this->nbkiData[$blockName]['lastname'],
            3 => $this->nbkiData[$blockName]['firstname'],
            4 => $this->nbkiData[$blockName]['surname'],
            5 => $this->nbkiData[$blockName]['issueDate'],
        ];
    }

    /** Содержимое поля 'C3_BIRTH' (RUTDF) */
    protected function add_C3_BIRTH() : array
    {
        $blockName = 'C3_BIRTH';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['birthDt'],
            2 => $this->nbkiData[$blockName]['OKSM'],
            3 => $this->nbkiData[$blockName]['placeOfBirth'],
        ];
    }

    /** Содержимое поля 'C4_ID' (RUTDF) */
    protected function add_C4_ID() : array
    {
        $blockName = 'C4_ID';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['OKSM'],
            2  => $this->nbkiData[$blockName]['otherCountry'],
            3  => $this->nbkiData[$blockName]['idType'],
            4  => $this->nbkiData[$blockName]['otherId'],
            5  => $this->nbkiData[$blockName]['seriesNumber'],
            6  => $this->nbkiData[$blockName]['idNum'],
            7  => $this->nbkiData[$blockName]['issueDate'],
            8  => $this->nbkiData[$blockName]['issueAuthority'],
            9  => $this->nbkiData[$blockName]['divCode'],
            10 => $this->nbkiData[$blockName]['validTo'],
        ];
    }

    /** Содержимое поля 'C5_PREVID' (RUTDF) */
    protected function add_C5_PREVID() : array
    {
        $blockName = 'C5_PREVID';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['isPrevId'],
            2  => $this->nbkiData[$blockName]['OKSM'],
            3  => $this->nbkiData[$blockName]['otherCountry'],
            4  => $this->nbkiData[$blockName]['idType'],
            5  => $this->nbkiData[$blockName]['otherId'],
            6  => $this->nbkiData[$blockName]['seriesNumber'],
            7  => $this->nbkiData[$blockName]['idNum'],
            8  => $this->nbkiData[$blockName]['issueDate'],
            9  => $this->nbkiData[$blockName]['issueAuthority'],
            10 => $this->nbkiData[$blockName]['divCode'],
            11 => $this->nbkiData[$blockName]['validTo'],
        ];
    }

    /** Содержимое поля 'C6_REGNUM' (RUTDF) */
    protected function add_C6_REGNUM() : array
    {
        $blockName = 'C6_REGNUM';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['taxpayerCode'],
            2 => $this->nbkiData[$blockName]['taxpayerNum'],
            3 => $this->nbkiData[$blockName]['regNum'],
            4 => $this->nbkiData[$blockName]['spectaxCode'],
        ];
    }

    /** Содержимое поля 'C7_SNILS' (RUTDF) */
    protected function add_C7_SNILS() : array
    {
        $blockName = 'C7_SNILS';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['SNILS'],
        ];
    }

    /** Содержимое поля 'DELETE' (RUTDF) */
    protected function add_DELETE() : array
    {
        $blockName = 'DELETE';

        return [
            0 => $blockName,
        ];
    }

    /** Содержимое поля 'C17_UID' (RUTDF) */
    protected function add_C17_UID() : array
    {
        $blockName = 'C17_UID';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['uuid'],
            2 => $this->nbkiData[$blockName]['acctNum'],
        ];
    }

    /** Содержимое поля 'C18_TRADE' (RUTDF) */
    protected function add_C18_TRADE() : array
    {
        $blockName = 'C18_TRADE';

        return [
            0 => $blockName,
            1  => $this->nbkiData[$blockName]['ownerIndic'],
            2  => $this->nbkiData[$blockName]['openedDt'],
            3  => $this->nbkiData[$blockName]['tradeTypeCode'],
            4  => $this->nbkiData[$blockName]['loanKindCode'],
            5  => $this->nbkiData[$blockName]['acctType'],
            6  => $this->nbkiData[$blockName]['isConsumerLoan'],
            7  => $this->nbkiData[$blockName]['hasCard'],
            8  => $this->nbkiData[$blockName]['isNovation'],
            9  => $this->nbkiData[$blockName]['isMoneySource'],
            10 => $this->nbkiData[$blockName]['isMoneyBorrower'],
            11 => $this->nbkiData[$blockName]['closeDt'],
            12 => $this->nbkiData[$blockName]['lendertypeCode'],
            13 => $this->nbkiData[$blockName]['obtainpartCred'],
            14 => $this->nbkiData[$blockName]['creditLine'],
            15 => $this->nbkiData[$blockName]['creditLineCode'],
            16 => $this->nbkiData[$blockName]['interestrateFloat'],
            17 => $this->nbkiData[$blockName]['transpartCred'],
            18 => $this->nbkiData[$blockName]['transpartCredUuid'],
            19 => $this->nbkiData[$blockName]['commitDate'],
        ];
    }

     /** Содержимое поля 'C19_ACCOUNTAMT' (RUTDF) */
    protected function add_C19_ACCOUNTAMT() : array
    {
        $blockName = 'C19_ACCOUNTAMT';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['creditLimit'],
            2 => $this->nbkiData[$blockName]['currencyCode'],
            3 => $this->nbkiData[$blockName]['ensuredAmt'],
            4 => $this->nbkiData[$blockName]['commitcurrCode'],
            5 => $this->nbkiData[$blockName]['commitCode'],
            6 => $this->nbkiData[$blockName]['amtDate'],
            7 => $this->nbkiData[$blockName]['commitUuid'],
        ];
    }

    /** Содержимое поля 'C21_PAYMTCONDITION' (RUTDF) */
    protected function add_C21_PAYMTCONDITION() : array
    {
        $blockName = 'C21_PAYMTCONDITION';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['principalTermsAmt'],
            2 => $this->nbkiData[$blockName]['principalTermsAmtDt'],
            3 => $this->nbkiData[$blockName]['interestTermsAmt'],
            4 => $this->nbkiData[$blockName]['interestTermsAmtDt'],
            5 => $this->nbkiData[$blockName]['termsFrequency'],
            6 => $this->nbkiData[$blockName]['minPaymt'],
            7 => $this->nbkiData[$blockName]['graceStartDt'],
            8 => $this->nbkiData[$blockName]['graceEndDt'],
            9 => $this->nbkiData[$blockName]['interestPaymentDueDate'],
        ];
    }

    /** Содержимое поля 'C22_OVERALLVAL' (RUTDF) */
    protected function add_C22_OVERALLVAL() : array
    {
        $blockName = 'C22_OVERALLVAL';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['creditTotalAmt'],
            2 => $this->nbkiData[$blockName]['creditTotalMonetaryAmt'],
            3 => $this->nbkiData[$blockName]['creditTotalAmtDate'],
        ];
    }

    /** Содержимое поля 'C24_FUNDDATE' (RUTDF) */
    protected function add_C24_FUNDDATE() : array
    {
        $blockName = 'C24_FUNDDATE';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['fundDate'],
            2 => $this->nbkiData[$blockName]['trancheNum'],
        ];
    }

    /** Содержимое поля 'C25_ARREAR' (RUTDF) */
    protected function add_C25_ARREAR() : array
    {
        $blockName = 'C25_ARREAR';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['isArrearExists'],
            2 => $this->nbkiData[$blockName]['startAmtOutstanding'],
            3 => $this->nbkiData[$blockName]['lastPaymentDueCode'],
            4 => $this->nbkiData[$blockName]['amtOutstanding'],
            5 => $this->nbkiData[$blockName]['principalOutstanding'],
            6 => $this->nbkiData[$blockName]['intOutstanding'],
            7 => $this->nbkiData[$blockName]['otherAmtOutstanding'],
            8 => $this->nbkiData[$blockName]['calcDate'],
            9 => $this->nbkiData[$blockName]['unconfirmGrace'],
        ];
    }

    /** Содержимое поля 'C26_DUEARREAR' (RUTDF) */
    protected function add_C26_DUEARREAR() : array
    {
        $blockName = 'C26_DUEARREAR';

        return [
            0 => $blockName,
            1 => $this->nbkiData[$blockName]['startDt'],
            2 => $this->nbkiData[$blockName]['lastPaymentDueCode'],
            3 => $this->nbkiData[$blockName]['amtOutstanding'],
            4 => $this->nbkiData[$blockName]['principalOutstanding'],
            5 => $this->nbkiData[$blockName]['intOutstanding'],
            6 => $this->nbkiData[$blockName]['otherAmtOutstanding'],
            7 => $this->nbkiData[$blockName]['calcDate'],
        ];
    }

    /** Содержимое поля 'C27_PASTDUEARREAR' (RUTDF) */
    protected function add_C27_PASTDUEARREAR() : array
    {
        $blockName = 'C27_PASTDUEARREAR';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['pastDueDt'],
            2  => $this->nbkiData[$blockName]['lastPaymentDueCode'],
            3  => $this->nbkiData[$blockName]['amtPastDue'],
            4  => $this->nbkiData[$blockName]['principalAmtPastDue'],
            5  => $this->nbkiData[$blockName]['intAmtPastDue'],
            6  => $this->nbkiData[$blockName]['otherAmtPastDue'],
            7  => $this->nbkiData[$blockName]['calcDate'],
            8  => $this->nbkiData[$blockName]['principalMissedDate'],
            9  => $this->nbkiData[$blockName]['intMissedDate'],
        ];
    }

    /** Содержимое поля 'C28_PAYMT' (RUTDF) */
    protected function add_C28_PAYMT() : array
    {
        $blockName = 'C28_PAYMT';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['paymtDate'],
            2  => $this->nbkiData[$blockName]['paymtAmt'],
            3  => $this->nbkiData[$blockName]['principalPaymtAmt'],
            4  => $this->nbkiData[$blockName]['intPaymtAmt'],
            5  => $this->nbkiData[$blockName]['otherPaymtAmt'],
            6  => $this->nbkiData[$blockName]['totalAmt'],
            7  => $this->nbkiData[$blockName]['principalTotalAmt'],
            8  => $this->nbkiData[$blockName]['intTotalAmt'],
            9  => $this->nbkiData[$blockName]['otherTotalAmt'],
            10 => $this->nbkiData[$blockName]['amtKeepCode'],
            11 => $this->nbkiData[$blockName]['termsDueCode'],
            12 => $this->nbkiData[$blockName]['daysPastDue'],
        ];
    }

    /** Содержимое поля 'C29_MONTHAVERPAYMT' (RUTDF) */
    protected function add_C29_MONTHAVERPAYMT() : array
    {
        $blockName = 'C29_MONTHAVERPAYMT';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['averPaymtAmt'],
            2  => $this->nbkiData[$blockName]['calcDate'],
        ];
    }

    /** Содержимое поля 'C38_OBLIGTERMINATION' (RUTDF) */
    protected function add_C38_OBLIGTERMINATION() : array
    {
        $blockName = 'C38_OBLIGTERMINATION';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['loanIndicator'],
            2  => $this->nbkiData[$blockName]['loanIndicatorDt'],
        ];
    }

    /** Содержимое поля 'C54_OBLIGACCOUNT' (RUTDF) */
    protected function add_C54_OBLIGACCOUNT() : array
    {
        $blockName = 'C54_OBLIGACCOUNT';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['obligAccountCode'],
            2  => $this->nbkiData[$blockName]['intRate'],
            3  => $this->nbkiData[$blockName]['offbalanceAmt'],
            4  => $this->nbkiData[$blockName]['preferenFinanc'],
            5  => $this->nbkiData[$blockName]['preferenFinancInfo'],
        ];
    }

    /** Содержимое поля 'C56_OBLIGPARTTAKE' (RUTDF) */
    protected function add_C56_OBLIGPARTTAKE() : array
    {
        $blockName = 'C56_OBLIGPARTTAKE';

        return [
            0  => $blockName,
            1  => $this->nbkiData[$blockName]['flagIndicatorCode'],
            2  => $this->nbkiData[$blockName]['approvedLoanTypeCode'],
            3  => $this->nbkiData[$blockName]['agreementNumber'],
            4  => $this->nbkiData[$blockName]['fundDt'],
            5  => $this->nbkiData[$blockName]['defaultFlag'],
            6  => $this->nbkiData[$blockName]['loanIndicator'],
        ];
    }


    /** Содержимое поля 'FOOTER' (RUTDF) */
    protected function add_FOOTER(int $clientsCount) : array
    {
        return [
            0 => 'TRAILER',
            1 => $clientsCount,
            2 => $this->blockCounter,
        ];
    }

    /** Вернуть НБКИ-блоки */
    public function getNbkiBlocks() : array
    {
        return $this->nbkiBlocks;
    }
}