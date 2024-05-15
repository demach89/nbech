<?php

namespace Core\NBKI;

/**
 * Класс добавления хелперов к НБКИ-блокам
 * НБКИ-блок (nbkiBlock) - массив с НБКИ-полями
 * Хелпер (helper) - строка, представленная в виде массива элементов, соответствующих НБКИ-блоку и его определённому полю
 * Хелпер-блок (helperBlock) - набор хелперов, относящихся к одному НБКИ-блоку и всем его полям
 *
 * Class NBKIHelper
 * @package Core\NBKI
 */
class NBKIHelper
{
    protected array $nbkiBlocks;
    protected array $helperBlocks;

    public function __construct(array $nbkiBlocks)
    {
        $this->nbkiBlocks = $nbkiBlocks;
    }

    /** Добавить блоки хелперов */
    public function addHelperBlocks() : array
    {
        foreach ($this->nbkiBlocks as $nbkiBlock) {
            if ($nbkiBlock[0] === '0_GROUPHEADER') {
                $this->helperBlocks[] = [''];
                $this->helperBlocks[] = ["=== № $nbkiBlock[1] ========================================================================================================================== |"];
                $this->helperBlocks[] = [''];
            }

            $this->helperBlocks[] = $nbkiBlock;

            $helperBlock = []; // Массив массивов

            switch ($nbkiBlock[0]) {
                case 'HEADER': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'HEADER'); break;
                case '0_GROUPHEADER': $helperBlock = $this->makeHelperBlock($nbkiBlock, '0_GROUPHEADER'); break;
                case 'C1_NAME': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C1_NAME'); break;
                case 'C2_PREVNAME': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C2_PREVNAME'); break;
                case 'C3_BIRTH': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C3_BIRTH'); break;
                case 'C4_ID': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C4_ID'); break;
                case 'C5_PREVID': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C5_PREVID'); break;
                case 'C6_REGNUM': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C6_REGNUM'); break;
                case 'C7_SNILS': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C7_SNILS'); break;
                case 'DELETE': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'DELETE'); break;
                case 'C17_UID': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C17_UID'); break;
                case 'C18_TRADE': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C18_TRADE'); break;
                case 'C19_ACCOUNTAMT': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C19_ACCOUNTAMT'); break;
                case 'C21_PAYMTCONDITION': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C21_PAYMTCONDITION'); break;
                case 'C22_OVERALLVAL': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C22_OVERALLVAL'); break;
                case 'C24_FUNDDATE': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C24_FUNDDATE'); break;
                case 'C25_ARREAR': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C25_ARREAR'); break;
                case 'C26_DUEARREAR': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C26_DUEARREAR'); break;
                case 'C27_PASTDUEARREAR': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C27_PASTDUEARREAR'); break;
                case 'C28_PAYMT': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C28_PAYMT'); break;
                case 'C29_MONTHAVERPAYMT': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C29_MONTHAVERPAYMT'); break;
                case 'C38_OBLIGTERMINATION': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C38_OBLIGTERMINATION'); break;
                case 'C54_OBLIGACCOUNT': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C54_OBLIGACCOUNT'); break;
                case 'C56_OBLIGPARTTAKE': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'C56_OBLIGPARTTAKE'); break;
                case 'TRAILER': $helperBlock = $this->makeHelperBlock($nbkiBlock, 'TRAILER'); break;
            }

            foreach ($helperBlock as $helper) {
                $this->helperBlocks[] = $helper;
            }

            $this->helperBlocks[] = [''];
        }

        return $this->helperBlocks;
    }

            /**
             * Создать набор helperBlock
             * @param array $nbkiBlock
             * @param string $blockMakerFunction
             * @return array
             */
            protected function makeHelperBlock(array $nbkiBlock, string $blockMakerFunction) : array
            {
                $helper = [];

                $fieldsCount = count($nbkiBlock);

                for ($fieldPos = $fieldsCount; $fieldPos !== 0; $fieldPos--) {
                    $helper[] = $this->addHelper($nbkiBlock, $fieldPos);
                }

                $blockMakerFunction = 'makeHelperBlock_' . $blockMakerFunction;

                // Использование обратного порядка обусловлено лучшей читаемостью вызываемой функции
                $helper = array_reverse($helper);
                $this->$blockMakerFunction($helper);
                $helper = array_reverse($helper);

                return $helper;
            }

                    /**
                     * Добавить хелпер
                     * @param $nbkiBlock
                     * @param $fieldPos
                     * @return array
                     */
                    protected function addHelper($nbkiBlock, $fieldPos) : array
                    {
                        $helperBlock = [];

                        for ($field = 0; $field < $fieldPos-1; $field++) {
                            $helperBlock[] = '|' . $this->makeEmptyStr($nbkiBlock[$field]);
                        }

                        $pos = $fieldPos-1;

                        $helperBlock[] = "|- [$pos] ";

                        return $helperBlock;
                    }

                            /**
                             * Имитировать переменную в виде пустой строки аналогичной длины
                             * @param string $field
                             * @return string
                             */
                            protected function makeEmptyStr(string $field) {

                                $fieldLen = mb_strlen($field);

                                if ($fieldLen === 0) {
                                    $fieldLen = 1;
                                }

                                return str_repeat(' ', $fieldLen-1);
                            }


    protected function makeHelperBlock_HEADER(array &$helper) : void
    {
        $helper[0][count($helper[0])-1]   .= 'Блок';
        $helper[1][count($helper[1])-1]   .= 'ИНН';
        $helper[2][count($helper[2])-1]   .= 'ОГРН';
        $helper[3][count($helper[3])-1]   .= 'Уникальный исходящий регистрационный номер документа (совпадает с именем файла без расширений)';
        $helper[4][count($helper[4])-1]   .= 'Регистрационная дата документа (указанная в имени файла)';
        $helper[5][count($helper[5])-1]   .= 'Поле не используется';
        $helper[6][count($helper[6])-1]   .= 'Имя пользователя для передачи данных';
        $helper[7][count($helper[7])-1]   .= 'Пароль';
        $helper[8][count($helper[8])-1]   .= 'Версия формата';
        $helper[9][count($helper[9])-1]   .= 'Исходящий регистрационный номер документа, не принятого ранее, - если передаются данные';
        $helper[10][count($helper[10])-1] .= 'Дата формирования документа, содержащего кредитную информацию';
        $helper[11][count($helper[11])-1] .= 'Входящий регистрационный номер документа, указанный в извещении от НБКИ о непринятии кредитной информации - если передаются данные';
        $helper[12][count($helper[12])-1] .= 'Дата документа, указанная в извещении от НБКИ о непринятии кредитной информации - если передаются данные, отвергнутые ранее';
    }

    protected function makeHelperBlock_0_GROUPHEADER(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Порядковый номер группы в файле (groupCounter)';
        $helper[2][count($helper[2])-1] .= 'Номер события, вследствие которого сформирована данная группа блоков (2.3 - для незакрытых, 2.5 - для закрытых) (eventNumber)';
        $helper[3][count($helper[3])-1] .= 'Код операции, в рамках которой сформирована группа блоков показателей (B – кредитная информация изменяется или дополняется) (operationCode)';
        $helper[4][count($helper[4])-1] .= 'Комментарий с пояснением причины представления группы блоков показателей (JSON, max 100 len) (comment)';
        $helper[5][count($helper[5])-1] .= 'Дата события, вследствие которого сформирована данная группа блоков (для закрытых - дата последнего платежа, иначе - текущая дата) (date)';
    }

    protected function makeHelperBlock_C1_NAME(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Фамилия (name1)';
        $helper[2][count($helper[2])-1] .= 'Имя (first)';
        $helper[3][count($helper[3])-1] .= 'Отчество (paternal)';
    }

    protected function makeHelperBlock_C2_PREVNAME(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Факт смены ФИО (isPrevName)';
        $helper[2][count($helper[2])-1] .= 'Прежняя фамилия КЛИЕНТА (name1)';
        $helper[3][count($helper[3])-1] .= 'Прежнее имя КЛИЕНТА (first)';
        $helper[4][count($helper[4])-1] .= 'Прежнее отчество КЛИЕНТА (paternal)';
        $helper[5][count($helper[5])-1] .= 'Дата выдачи документа с измененным именем (issueDate)';
    }

    protected function makeHelperBlock_C3_BIRTH(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Дата рождения (birthDt)';
        $helper[2][count($helper[2])-1] .= 'Цифровой код страны согласно Общероссийскому классификатору стран мира (ОКСМ) (OKSM)';
        $helper[3][count($helper[3])-1] .= 'Место рождения клиента (placeOfBirth)';
    }

    protected function makeHelperBlock_C4_ID(array &$helper) : void
    {
        $helper[0][count($helper[0])-1]   .= 'Блок';
        $helper[1][count($helper[1])-1]   .= 'Цифровой код страны согласно Общероссийскому классификатору стран мира (ОКСМ) (OKSM)';
        $helper[2][count($helper[2])-1]   .= 'Заполняется, если по показателю «Код страны по ОКСМ» указано «999» //otherCountry';
        $helper[3][count($helper[3])-1]   .= 'Код документа (21 = паспорт) (idType)';
        $helper[4][count($helper[4])-1]   .= 'Наименование иного документа (otherId)';
        $helper[5][count($helper[5])-1]   .= 'Серия паспорта (seriesNumber)';
        $helper[6][count($helper[6])-1]   .= 'Номер паспорта (idNum)';
        $helper[7][count($helper[7])-1]   .= 'Дата выдачи паспорта (issueDate)';
        $helper[8][count($helper[8])-1]   .= 'Кем выдан паспорт (issueAuthority)';
        $helper[9][count($helper[9])-1]   .= 'Код подразделения (divCode)';
        $helper[10][count($helper[10])-1] .= 'Дата окончания срока действия документа (validTo)';
    }

    protected function makeHelperBlock_C5_PREVID(array &$helper) : void
    {
        $helper[0][count($helper[0])-1]   .= 'Блок';
        $helper[1][count($helper[1])-1]   .= 'Признак наличия документа (0 - нет, 1 - да). При 0 остальные пустые (isPrevId)';
        $helper[2][count($helper[2])-1]   .= 'Цифровой код страны согласно Общероссийскому классификатору стран мира (ОКСМ). При отсутствии страны в ОКСМ указывается «999». (OKSM)';
        $helper[3][count($helper[3])-1]   .= 'Наименование иной страны. Заполняется, если по показателю «Код страны по ОКСМ» указано «999». При отсутствии у субъекта гражданства указывается «гражданство отсутствует» (otherCountry)';
        $helper[4][count($helper[4])-1]   .= 'Код документа (idType)';
        $helper[5][count($helper[5])-1]   .= 'Наименование иного документа. Заполняется, если по показателю «Код документа» указано «999» (otherId)';
        $helper[6][count($helper[6])-1]   .= 'Серия документа (seriesNumber)';
        $helper[7][count($helper[7])-1]   .= 'Номер документа (idNum)';
        $helper[8][count($helper[8])-1]   .= 'Дата выдачи документа (issueDate)';
        $helper[9][count($helper[9])-1]   .= 'Кем выдан документ (issueAuthority)';
        $helper[10][count($helper[10])-1] .= 'Код подразделения (divCode)';
        $helper[11][count($helper[11])-1] .= 'Дата окончания срока действия документа (validTo)';
    }

    protected function makeHelperBlock_C6_REGNUM(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Код номера налогоплательщика (taxpayerCode)';
        $helper[2][count($helper[2])-1] .= 'Номер налогоплательщика (taxpayerNum)';
        $helper[3][count($helper[3])-1] .= 'Регистрационный номер (regNum)';
        $helper[4][count($helper[4])-1] .= 'Признак специального налогового режима (spectaxCode)';
    }

    protected function makeHelperBlock_C7_SNILS(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'СНИЛС (SNILS)';
    }

    protected function makeHelperBlock_DELETE(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
    }

    protected function makeHelperBlock_C17_UID(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'УИД сделки (uuid)';
        $helper[2][count($helper[2])-1] .= 'Номер сделки (acctNum)';
    }

    protected function makeHelperBlock_C18_TRADE(array &$helper) : void
    {
        $helper[0][count($helper[0])-1]   .= 'Блок';
        $helper[1][count($helper[1])-1]   .= 'Код участника (1 - заёмщик) (ownerIndic)';
        $helper[2][count($helper[2])-1]   .= 'Дата выдачи (openedDt)';
        $helper[3][count($helper[3])-1]   .= 'Тип сделки (1 - договор займа) (tradeTypeCode)';
        $helper[4][count($helper[4])-1]   .= 'Тип займа (13 - Необеспеченный микрозаем) (loanKindCode)';
        $helper[5][count($helper[5])-1]   .= 'Цель кредита (99 - иное) (acctType)';
        $helper[6][count($helper[6])-1]   .= 'Является кредитом (1 - да, 0 - нет) (isConsumerLoan)';
        $helper[7][count($helper[7])-1]   .= 'Признак использования платежной карты (1 - да, 0 - нет) (hasCard)';
        $helper[8][count($helper[8])-1]   .= 'Признак возникновения обязательства в результате новации (1 - да, 0 - нет) (isNovation)';
        $helper[9][count($helper[9])-1]   .= 'Признак денежного обязательства источника (1 - да, 0 - нет) (isMoneySource)';
        $helper[10][count($helper[10])-1] .= 'Признак денежного обязательства субъекта (1 - да, 0 - нет) (isMoneyBorrower)';
        $helper[11][count($helper[11])-1] .= 'Дата прекращения обязательства субъекта по условиям сделки (closeDt)';
        $helper[12][count($helper[12])-1] .= 'Лицо, осуществляющее деятельность по возврату просроченной задолженности (2 = Заимодавец – микрофинансовая организация) (lendertypeCode)';
        $helper[13][count($helper[13])-1] .= 'Признак возникновения обязательства в результате получения части прав кредитора от другого лица (obtainpartCred)';
        $helper[14][count($helper[14])-1] .= 'Признак кредитной линии (creditLine)';
        $helper[15][count($helper[15])-1] .= 'Код типа кредитной линии (creditLineCode)';
        $helper[16][count($helper[16])-1] .= 'Признак плавающей (переменной) процентной ставки (interestrateFloat)';
        $helper[17][count($helper[17])-1] .= 'Признак частичной передачи прав кредитора другому лицу. Указывается источником, осуществившим частичную передачу прав кредитора по обязательству другому источнику (transpartCred)';
        $helper[18][count($helper[18])-1] .= 'УИд сделки, по которой права кредитора частично переданы другому лицу. Заполняется источником, к которому частично перешли права требования по обязательству субъекта (transpartCredUuid)';
        $helper[19][count($helper[19])-1] .= 'Дата возникновения обязательства субъекта. Указывается дата возникновения у субъекта обязательства в силу закона или по соглашению сторон (commitDate)';
    }

    protected function makeHelperBlock_C19_ACCOUNTAMT(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Сумма обязательства (creditLimit)';
        $helper[2][count($helper[2])-1] .= 'Валюта обязательства (currencyCode)';
        $helper[3][count($helper[3])-1] .= 'Сумма обеспечиваемого обязательства. Заполняется, если обязательством субъекта обеспечивается исполнение другого обязательства (ensuredAmt)';
        $helper[4][count($helper[4])-1] .= 'Валюта обеспечиваемого обязательства (commitcurrCode)';
        $helper[5][count($helper[5])-1] .= 'Код типа обеспечиваемого обязательства (commitCode)';
        $helper[6][count($helper[6])-1] .= 'Дата расчета (amtDate)';
        $helper[7][count($helper[7])-1] .= 'УИд сделки, в результате которой возникло обеспечиваемое обязательство (commitUuid)';
    }

    protected function makeHelperBlock_C21_PAYMTCONDITION(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Сумма ближайшего следующего платежа по основному долгу (для действующих - срочный остаток ОД, для истёкших - "0,00") (principalTermsAmt)';
        $helper[2][count($helper[2])-1] .= 'Дата ближайшего следующего платежа по основному долгу (для действующих - дата  возврата, для истёкших - "") (principalTermsAmtDt)';
        $helper[3][count($helper[3])-1] .= 'Сумма ближайшего следующего платежа по процентам (для действующих - срочный остаток ОП, для истёкших - "0,00") (interestTermsAmt)';
        $helper[4][count($helper[4])-1] .= 'Дата ближайшего следующего платежа по процентам (для действующих - дата  возврата, для истёкших - "") (interestTermsAmtDt)';
        $helper[5][count($helper[5])-1] .= 'Код частоты платежей (99 - иное) (termsFrequency)';
        $helper[6][count($helper[6])-1] .= 'Сумма минимального платежа по кредитной карте (не заполняется) (minPaymt)';
        $helper[7][count($helper[7])-1] .= 'Дата начала беспроцентного периода (не заполняется) (graceStartDt)';
        $helper[8][count($helper[8])-1] .= 'Дата окончания беспроцентного периода (не заполняется) (graceEndDt)';
        $helper[9][count($helper[9])-1] .= 'Дата окончания срока уплаты процентов (дата возврата) (interestPaymentDueDate)';
    }

    protected function makeHelperBlock_C22_OVERALLVAL(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'ПСК в % (creditTotalAmt)';
        $helper[2][count($helper[2])-1] .= 'ПСК в руб (creditTotalMonetaryAmt)';
        $helper[3][count($helper[3])-1] .= 'ПСК дата расчёта (creditTotalAmtDate)';
    }

    protected function makeHelperBlock_C24_FUNDDATE(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Дата передачи финансирования субъекту или возникновения обеспечения исполнения обязательства (fundDate)';
        $helper[2][count($helper[2])-1] .= 'Порядковый номер транша. Заполняется для займа (кредита), который выдается траншами (trancheNum)';
    }

    protected function makeHelperBlock_C25_ARREAR(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Признак наличия задолженности (1 – у субъекта имеется задолженность перед источником, 0 - нет) (isArrearExists)';
        $helper[2][count($helper[2])-1] .= 'Сумма задолженности на дату передачи финансирования субъекту или возникновения обеспечения исполнения обязательства (сумма займа) (startAmtOutstanding)';
        $helper[3][count($helper[3])-1] .= 'Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу, 0 – прошло 30 дней с даты последнего расчета; пусто - иначе) (lastPaymentDueCode)';
        $helper[4][count($helper[4])-1] .= 'Сумма задолженности (amtOutstanding)';
        $helper[5][count($helper[5])-1] .= 'Сумма задолженности по основному долгу (principalOutstanding)';
        $helper[6][count($helper[6])-1] .= 'Сумма задолженности по процентам (intOutstanding)';
        $helper[7][count($helper[7])-1] .= 'Сумма задолженности по иным требованиям (ШП) (otherAmtOutstanding)';
        $helper[8][count($helper[8])-1] .= 'Дата расчет (calcDate)';
        $helper[9][count($helper[9])-1] .= 'Признак неподтвержденного льготного периода (0) (unconfirmGrace)';
    }

    protected function makeHelperBlock_C26_DUEARREAR(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Дата возникновения срочной задолженности (дата выдачи) (startDt)';
        $helper[2][count($helper[2])-1] .= 'Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе) (lastPaymentDueCode)';
        $helper[3][count($helper[3])-1] .= 'Сумма срочной задолженности (сроч остаток ОД+ОП) (Если указано значение "0,00", иные показатели блока не заполняются) (amtOutstanding)';
        $helper[4][count($helper[4])-1] .= 'Сумма срочной задолженности по основному долгу (principalOutstanding)';
        $helper[5][count($helper[5])-1] .= 'Сумма срочной задолженности по процентам (intOutstanding)';
        $helper[6][count($helper[6])-1] .= 'Сумма срочной задолженности по иным требованиям ("0,00", ШП учитываются только в просрочке) (otherAmtOutstanding)';
        $helper[7][count($helper[7])-1] .= 'Дата расчета (не заполняется при 0)(calcDate)';
    }

    protected function makeHelperBlock_C27_PASTDUEARREAR(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Дата возникновения просроченной задолженности (дата выхода на просрочку) (pastDueDt)';
        $helper[2][count($helper[2])-1] .= 'Признак расчета по последнему платежу (1 – субъект внес платеж либо наступил срок для внесения платежа по срочному долгу; 0 - прошло 30 дней с последнего расчёта; пусто - иначе) (lastPaymentDueCode)';
        $helper[3][count($helper[3])-1] .= 'Сумма просроченной задолженности (с учётом ШП) (amtPastDue)';
        $helper[4][count($helper[4])-1] .= 'Сумма просроченной задолженности по основному долгу (principalAmtPastDue)';
        $helper[5][count($helper[5])-1] .= 'Сумма просроченной задолженности по процентам (intAmtPastDue)';
        $helper[6][count($helper[6])-1] .= 'Сумма просроченной задолженности по иным требованиям (ШП) (otherAmtPastDue)';
        $helper[7][count($helper[7])-1] .= 'Дата расчёта (calcDate)';
        $helper[8][count($helper[8])-1] .= 'Дата последнего пропущенного платежа по основному долгу (дата выхода на просрочку; иначе - пусто) (principalMissedDate)';
        $helper[9][count($helper[9])-1] .= 'Дата последнего пропущенного платежа по процентам (дата выхода на просрочку; иначе - пусто) (intMissedDate)';
    }

    protected function makeHelperBlock_C28_PAYMT(array &$helper) : void
    {
        $helper[0][count($helper[0])-1]   .= 'Блок';
        $helper[1][count($helper[1])-1]   .= 'Дата последнего внесенного платежа (paymtDate)';
        $helper[2][count($helper[2])-1]   .= 'Сумма последнего внесенного платежа (paymtAmt)';
        $helper[3][count($helper[3])-1]   .= 'Сумма последнего внесенного платежа по основному долгу (principalPaymtAmt)';
        $helper[4][count($helper[4])-1]   .= 'Сумма последнего внесенного платежа по процентам (intPaymtAmt)';
        $helper[5][count($helper[5])-1]   .= 'Сумма последнего внесенного платежа по иным требованиям (otherPaymtAmt)';
        $helper[6][count($helper[6])-1]   .= 'Сумма всех внесенных платежей по обязательству (totalAmt)';
        $helper[7][count($helper[7])-1]   .= 'Сумма внесенных платежей по основному долгу (principalTotalAmt)';
        $helper[8][count($helper[8])-1]   .= 'Сумма внесенных платежей по процентам (intTotalAmt)';
        $helper[9][count($helper[9])-1]   .= 'Сумма внесенных платежей по иным требованиям (ШП) (otherTotalAmt)';
        $helper[10][count($helper[10])-1] .= 'Код соблюдения размера платежей (1 - Платеж внесен в полном размере, 2 - Платеж внесен не в полном размере, 3 - Платеж не внесен) (amtKeepCode)';
        $helper[11][count($helper[11])-1] .= 'Код соблюдения срока внесения платежей (2 - Платежи вносятся своевременно, 3 - Платежи вносятся несвоевременно) (termsDueCode)';
        $helper[12][count($helper[12])-1] .= 'Продолжительность просрочки (равен нулю при закрытии займа (правка от 2023-10-04) (daysPastDue)';
    }

    protected function makeHelperBlock_C29_MONTHAVERPAYMT(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Величина среднемесячного платежа (averPaymtAmt)';
        $helper[2][count($helper[2])-1] .= 'Дата расчета величины среднемесячного платежа (calcDate)';
    }

    protected function makeHelperBlock_C38_OBLIGTERMINATION(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Код основания прекращения обязательства (1 - Надлежащее исполнение обязательства) (loanIndicator)';
        $helper[2][count($helper[2])-1] .= 'Дата фактического прекращения обязательства (loanIndicatorDt)';
    }

    protected function makeHelperBlock_C54_OBLIGACCOUNT(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Обязательство учтено у источника на балансовых счетах (1 - да, 0 - нет) (obligAccountCode)';
        $helper[2][count($helper[2])-1] .= 'Процентная ставка (intRate)';
        $helper[3][count($helper[3])-1] .= 'Сумма обязательства, учтенная на внебалансовых счетах (offbalanceAmt)';
        $helper[4][count($helper[4])-1] .= 'Признак льготного финансирования с государственной поддержкой (1 - да, 0 - нет) (preferenFinanc)';
        $helper[5][count($helper[5])-1] .= 'Информация о программе государственной поддержки (preferenFinancInfo)';
    }

    protected function makeHelperBlock_C56_OBLIGPARTTAKE(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Код вида участия в сделке (1 - Заемщик) (flagIndicatorCode)';
        $helper[2][count($helper[2])-1] .= 'Тип займа (13 - Необеспеченный микрозаем) (approvedLoanTypeCode)';
        $helper[3][count($helper[3])-1] .= 'УИД (agreementNumber)';
        $helper[4][count($helper[4])-1] .= 'Дата передачи финансирования субъекту (fundDt)';
        $helper[5][count($helper[5])-1] .= 'Признак просрочки должника более 90 дней (0 - нет, 1 - да) (равен нулю при закрытии займа (правка от 2023-10-04)) (defaultFlag)';
        $helper[6][count($helper[6])-1] .= 'Признак прекращения обязательства (0 - займ действующий, 1 - займ закрыт) (loanIndicator)';
    }

    protected function makeHelperBlock_TRAILER(array &$helper) : void
    {
        $helper[0][count($helper[0])-1] .= 'Блок';
        $helper[1][count($helper[1])-1] .= 'Количество субъектов в файле';
        $helper[2][count($helper[2])-1] .= 'Количество групп блоков в файле';
    }
}