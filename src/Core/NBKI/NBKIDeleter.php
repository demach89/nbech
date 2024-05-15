<?php

namespace Core\NBKI;

require_once __DIR__ . '/nbkiFunc.php';

/**
 * Класс подготовки НБКИ-выгрузки по удалению заявки из НБКИ
 * - сопоставляет поля реестра с параметрами НБКИ (RUTDF)
 * - запускает подготовку НБКИ-блоков из параметров НБКИ (NBKIConstructor)
 *
 * Class NBKIDeleter
 * @package Core\NBKI
 */
class NBKIDeleter extends NBKICreator
{
    /**
     * Сопоставить поля реестра с параметрами НБКИ (RUTDF)
     * @return array
     */
    protected function convertRegToNBKIData() : array
    {
        $nbkiData = [];
        
        foreach ($this->regData as $regDatum) {
            $nbkiData [] = [
                '0_GROUPHEADER'         => $this->get_0_GROUPHEADER($regDatum),
                'C1_NAME'               => $this->get_C1_NAME($regDatum),
                'C2_PREVNAME'           => $this->get_C2_PREVNAME($regDatum),
                'C3_BIRTH'              => $this->get_C3_BIRTH($regDatum),
                'C4_ID'                 => $this->get_C4_ID($regDatum),
                'C5_PREVID'             => $this->get_C5_PREVID($regDatum),
                'C7_SNILS'              => $this->get_C7_SNILS($regDatum),
                'DELETE'                => $this->get_DELETE(),
                'C17_UID'               => $this->get_C17_UID($regDatum),
                'C18_TRADE'             => $this->get_C18_TRADE($regDatum),
                'C19_ACCOUNTAMT'        => $this->get_C19_ACCOUNTAMT($regDatum),
            ];
        }

        return $nbkiData;
    }

    /** Создать НБКИ-блоки из набора данных для НБКИ */
    protected function makeNBKIBlocks() : self
    {
        $NBKIConstructor = new NBKIConstructor($this->regTimestamp);

        $NBKIConstructor->addHeader($this->filename);

        foreach ($this->nbkiData as $nbkiDatum) {
            $NBKIConstructor->addContentToDelete($nbkiDatum);
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
            'eventNumber'   => '3.3',            // 2 // Номер события, вследствие которого сформирована данная группа блоков
            'operationCode' => 'C.2',            // 3 // Код операции, в рамках которой сформирована группа блоков показателей (B – кредитная информация изменяется или дополняется)
            'comment'       => '',               // 4 // Коммент
            'date'          => $this->dateDMY,   // 5 // Дата события, вследствие которого сформирована данная группа блоков (для закрытых - дата последнего платежа, иначе - текущая дата)
        ];
    }
}