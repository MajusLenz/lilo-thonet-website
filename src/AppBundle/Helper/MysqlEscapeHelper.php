<?php

namespace AppBundle\Helper;

class MysqlEscapeHelper
{
    
    /**
     * @param $string String unescapter String
     * @return String fuer MySql-Queries escapter String
     */
    public static function escape($string)
    {
        $string = str_replace('\\', '\\\\', $string);
        $string = str_replace("'", "\\'", $string);
        $string = str_replace('"', '\\"', $string);
        $string = str_replace('%', '\%', $string);
        $string = str_replace('_', '\_', $string);
        return $string;
    }
}
