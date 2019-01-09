<?php

namespace AppBundle\Helper;

class HashHelper
{

    /**
     * @param $dateiHash String DateiHash einer Entity\Archivierung
     * @return array bildordner und namen zu den versch. grossen Bildern der Archivierung
     */
    public static function dateiHashToURL($dateiHash)
    {
        $ersteZweiBuchstaben = substr($dateiHash, 0, 2);
        $hashRest = substr($dateiHash, 2);
        $pfad = $ersteZweiBuchstaben . "/" . $hashRest;

        return array(
            "gross" => $pfad . "_g" . ".png",
            "mittel" => $pfad . "_m" . ".png",
            "klein" => $pfad . "_k" . ".png"
        );
    }
}
