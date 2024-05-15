<?php

namespace Core\Provider\MSSQL\Query;

class NBKIQuery
{
    public static function get() : string
    {
        return <<< SQL
            SELECT reports.*, history.formDate 
              FROM EF_NBKI_SEND_HISTORY AS history 
              LEFT JOIN EF_NBKI_SEND_REPORTS AS reports 
                ON reports.OID = history.OID 
             WHERE history.enabled = 1
        SQL;
    }

    public static function getAfterDate() : string
    {
        return <<< SQL
            SELECT reports.*, history.formDate  
              FROM EF_NBKI_SEND_HISTORY AS history 
              LEFT JOIN EF_NBKI_SEND_REPORTS AS reports 
                ON reports.OID = history.OID 
             WHERE history.formDate >= ?
               AND history.enabled = 1 
        SQL;
    }

    public static function getByContract() : string
    {
        return <<< SQL
            SELECT reports.*, history.formDate  
              FROM EF_NBKI_SEND_HISTORY AS history 
              LEFT JOIN EF_NBKI_SEND_REPORTS AS reports 
                ON reports.OID = history.OID 
             WHERE reports.contract = ?
        SQL;
    }

    public static function getByUID() : string
    {
        return <<< SQL
            SELECT reports.*, history.formDate  
              FROM EF_NBKI_SEND_HISTORY AS history 
              LEFT JOIN EF_NBKI_SEND_REPORTS AS reports 
                ON reports.OID = history.OID 
             WHERE reports.uid = ?
        SQL;
    }

    public static function excludeContract() : string
    {
        return <<< SQL
            UPDATE EF_NBKI_SEND_HISTORY 
               SET enabled = 0
             WHERE contract = ?
        SQL;
    }

    public static function includeContract() : string
    {
        return <<< SQL
            UPDATE EF_NBKI_SEND_HISTORY 
               SET enabled = 1
             WHERE contract = ?
        SQL;
    }
}


