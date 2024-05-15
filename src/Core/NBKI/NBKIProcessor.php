<?php

namespace Core\NBKI;

require_once __DIR__ . '/nbkiFunc.php';

/**
 * Класс подготовки НБКИ-выгрузки
 * - сопоставляет поля реестра с параметрами НБКИ (RUTDF)
 * - запускает подготовку НБКИ-блоков из параметров НБКИ (NBKIConstructor)
 *
 * Class NBKIProcessor
 * @package Core\NBKI
 */
class NBKIProcessor extends NBKICreator
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
                'C6_REGNUM'             => $this->get_C6_REGNUM($regDatum),
                'C7_SNILS'              => $this->get_C7_SNILS($regDatum),
                'C17_UID'               => $this->get_C17_UID($regDatum),
                'C18_TRADE'             => $this->get_C18_TRADE($regDatum),
                'C19_ACCOUNTAMT'        => $this->get_C19_ACCOUNTAMT($regDatum),
                'C21_PAYMTCONDITION'    => $this->get_C21_PAYMTCONDITION($regDatum),
                'C22_OVERALLVAL'        => $this->get_C22_OVERALLVAL($regDatum),
                'C24_FUNDDATE'          => $this->get_C24_FUNDDATE($regDatum),
                'C25_ARREAR'            => $this->get_C25_ARREAR($regDatum),
                'C26_DUEARREAR'         => $this->get_C26_DUEARREAR($regDatum),
                'C27_PASTDUEARREAR'     => $this->get_C27_PASTDUEARREAR($regDatum),
                'C28_PAYMT'             => $this->get_C28_PAYMT($regDatum),
                'C29_MONTHAVERPAYMT'    => $this->get_C29_MONTHAVERPAYMT($regDatum),
                'C38_OBLIGTERMINATION'  => $this->get_C38_OBLIGTERMINATION($regDatum),
                'C54_OBLIGACCOUNT'      => $this->get_C54_OBLIGACCOUNT($regDatum),
                'C56_OBLIGPARTTAKE'     => $this->get_C56_OBLIGPARTTAKE($regDatum),
            ];
        }

        return $nbkiData;
    }
}