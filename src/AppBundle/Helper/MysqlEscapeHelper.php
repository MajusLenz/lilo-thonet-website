<?php

namespace AppBundle\Helper;

class MysqlEscapeHelper
{
    
    /**
     * @param $string String unescapter String
     * @param $isLikeSearch boolean gibt an ob der escapte String als für eine LIKE suche verwndet werden soll.
     * in diesem fall werden auch "%" und "_" escapet.
     * @return String fuer MySql-Queries escapter String
     */
    public static function escape($string, $isLikeSearch)
    {
        $string = str_replace('\\', '\\\\', $string);
        $string = str_replace("'", "\\'", $string);
        $string = str_replace('"', '\\"', $string);
        if($isLikeSearch) {
            $string = str_replace('%', '\%', $string);
            $string = str_replace('_', '\_', $string);
        }
        return $string;
    }
}
