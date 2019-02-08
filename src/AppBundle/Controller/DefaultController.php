<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Archivierung;
use AppBundle\Helper\MysqlEscapeHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Helper\HashHelper;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/DasProjekt/", name="_dasProjekt")
     */
    public function dasProjektAction()
    {
        // replace this example code with whatever you need
        return $this->render('default/dasProjekt.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/ArbeitenImKontext/", name="_arbeitenImKontext")
     */
    public function arbeitenImKontextAction()
    {
        // replace this example code with whatever you need
        return $this->render('default/arbeitenImKontext.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/MoebelArchiv/", name="_moebelArchivKontext")
     */
    public function moebelArchivAction(Request $request)
    {
        return $this->indexAction($request);
    }

    /**
     * @Route("/Kontakt/", name="_kontakt")
     */
    public function kontaktAction()
    {
        // replace this example code with whatever you need
        return $this->render('default/kontakt.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/", name="_index")
     */
    public function indexAction(Request $request)
    {

        return $this->sucheAction($request);
    }


    /**
     * @Route("/suche/", name="_suche")
     */
    public function sucheAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('AppBundle:Archivierung', 'a');

        $sql = ""."SELECT MIN(wert) AS 'min', MAX(wert) AS 'max' FROM Jahr";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $stmtResult = $stmt->fetchAll();

        $minDBJahr = $stmtResult[0]["min"];   // kleinstes Jahr in der DB
        $maxDBJahr = $stmtResult[0]["max"];   // groesstes Jahr in der DB

        $allParams = $request->query->all();


        // speichere die Auswahl der Text-Suchfilter des Requests ab. geordnet nach infoNamen:
        $auswahl = array();

        foreach($allParams as $infoName => $infoParam) {

            if($infoName != "Tags" && $infoName != "Jahr") {
                $infoAuswahl = array();
                $infoArray = explode(";", $infoParam);

                foreach($infoArray as $infoWert) {
                    $infoWert = trim($infoWert);

                    if(empty($infoWert))
                        continue;

                    array_push($infoAuswahl, $infoWert);
                }

                $auswahl[$infoName] = $infoAuswahl;
            }
        }


        // speichere die Auswahl der Jahr-Suchfilter des Requests ab:
        $minAuswahlJahr = $minDBJahr;
        $maxAuswahlJahr = $maxDBJahr;

        if( array_key_exists("Jahr", $allParams) ) {

            $jahrArray = explode(";", $allParams["Jahr"]);
            $minAuswahlJahr = $jahrArray[0];
            $maxAuswahlJahr = $jahrArray[1];
        }

        $auswahl["Jahr"] = array("min" => $minAuswahlJahr, "max" => $maxAuswahlJahr);



        // Hole alle verschiedenen infoNamen die es gibt aus der DB:
        $sql = ""."SELECT DISTINCT i.name FROM Information i ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $alleInfoNamen = $stmt->fetchAll();


        // Hole Vorschlaege für alle infoNamen aus der DB:
        $vorschlaege = array();

        // Freitext-Vorschlaege
        $freitextAuswahlEscaped = array();

        if( array_key_exists("Freitext", $auswahl) ) {

            foreach ($auswahl["Freitext"] as $freitextAuswahlItem) {

                $itemEscaped = MysqlEscapeHelper::escape($freitextAuswahlItem, false);;
                array_push($freitextAuswahlEscaped, $itemEscaped);
            }
        }

        $freitextVorschlaege = $this->vorschlaegeSuche("Freitext", null, $freitextAuswahlEscaped);
        $vorschlaege["Freitext"] = $freitextVorschlaege;


        // Restliche Vorschlaege
        foreach($alleInfoNamen as $infoName) {
            $infoName = $infoName["name"];

            if($infoName != "Tags") {
                $infoAuswahlEscaped = array();

                if( array_key_exists($infoName, $auswahl) ) {

                    foreach ($auswahl[$infoName] as $infoAuswahlItem) {

                        $itemEscaped = MysqlEscapeHelper::escape($infoAuswahlItem, false);;
                        array_push($infoAuswahlEscaped, $itemEscaped);
                    }
                }

                $infoVorschlaege = $this->vorschlaegeSuche($infoName, null, $infoAuswahlEscaped);

                $vorschlaege[$infoName] = $infoVorschlaege;
            }
        }


        // Wenn keine Suchparameter angegeben, sofort alle Archivierungen zurückgeben
        if( empty($allParams) ) {
            $sql =
                "SELECT a.* " .
                "FROM Archivierung a " .
                "ORDER BY a.erstelldatum DESC";
            $query = $em->createNativeQuery($sql, $rsm);
            $alleArchivierungen = $query->getResult();

            return $this->createSuchResponse($alleArchivierungen, $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl);
        }


        // SUCHE:::::::::

        // FREITEXT-SUCHE:

        $freitextParam = $allParams["Freitext"];
        unset($allParams["Freitext"]);

        $freitextArray = explode(";", $freitextParam);

        $freitextWerte = "";
        $freitextJahre = "";
        $isFirst = true;

        foreach ($freitextArray as $freitextWert) {
            $freitextWert = trim($freitextWert);

            if (empty($freitextWert))
                continue;

            $freitextWert = MysqlEscapeHelper::escape($freitextWert, true);

            if ($isFirst) {
                $freitextJahre = "LOWER('%$freitextWert%')";
                $freitextWerte = "LOWER('%$freitextWert%')";
                $isFirst = false;
            } else {
                $freitextJahre .= " OR j.wert LIKE '%$freitextWert%' ";
                $freitextWerte .= " OR LOWER(i.wert) LIKE LOWER('%$freitextWert%') ";
            }
        }

        if( !empty($freitextWerte) ) {

            $sql =
                "SELECT a.* " .
                "FROM Archivierung a " .
                "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
                "LEFT JOIN Information i ON ai.information_id = i.id " .
                "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
                "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
                "WHERE ( ( LOWER(i.wert) LIKE $freitextWerte ) AND i.name != 'Tags' ) " .
                "   OR ( j.wert LIKE $freitextJahre ) " .
                "ORDER BY a.erstelldatum DESC"
            ;
            $query = $em->createNativeQuery($sql, $rsm);
            $freitextErgebnis = $query->getResult();

            // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
            if(empty($freitextErgebnis))
                return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl);

            else
                $gesamtErgebnis = $freitextErgebnis;
        }

        // Falls Freitext leer ist, Das Ergebnis mit allen Archivierungen initialisieren für die nachfolgenden Schnittmengen-Operationen
        else{
            $sql =
                "SELECT a.* " .
                "FROM Archivierung a " .
                "ORDER BY a.erstelldatum DESC";
            $query = $em->createNativeQuery($sql, $rsm);
            $gesamtErgebnis = $query->getResult();
        }


        // JAHR-FILTER:

        $jahrParam = trim($allParams["Jahr"]);
        unset($allParams["Jahr"]);

        $minJahr = 0;
        $maxJahr = 0;
        $jahreArray = explode(";", $jahrParam);

        // Fehler wenn Jahre in falschem Format angegeben
        if (count($jahreArray) > 2) {
            throw $this->createNotFoundException('Falsches Jahresformat!');
        }

        // nur ein Jahr
        if (count($jahreArray) === 1) {
            $minJahr = trim($jahreArray[0]);
            $maxJahr = $minJahr;
        }
        // mehrere Jahre
        elseif (count($jahreArray) === 2) {
            $minJahr = trim($jahreArray[0]);
            $maxJahr = trim($jahreArray[1]);
        }

        // min und max tauschen falls falschherum
        if ($minJahr > $maxJahr) {
            $temp = $maxJahr;
            $maxJahr = $minJahr;
            $minJahr = $temp;
        }

        // Fehler wenn eines der Jahre keine Zahl ist
        if (!is_numeric($minJahr) || !is_numeric($maxJahr)) {
            throw $this->createNotFoundException('Falsches Jahresformat!');
        }

        // Nur nach Jahren filtern, wenn der User den Jahr-Slider ueberhaupt benutzt hat
        if($minJahr != $minDBJahr || $maxJahr != $maxDBJahr) {

            $jahrString = "$minJahr";

            for ($i = $minJahr + 1; $i <= $maxJahr; $i++) {
                $jahrString = $jahrString . ", $i";
            }

            $sql =
                "SELECT a.* " .
                "FROM Archivierung a " .
                "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
                "LEFT JOIN Information i ON ai.information_id = i.id " .
                "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
                "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
                "WHERE j.wert IN($jahrString) " .
                "ORDER BY a.erstelldatum DESC"
            ;
            $query = $em->createNativeQuery($sql, $rsm);
            $jahrErgebnis = $query->getResult();

            // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
            if(empty($jahrErgebnis))
                return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl);

            else
                // Schnittmenge bilden
                $gesamtErgebnis = array_uintersect(
                    $gesamtErgebnis,
                    $jahrErgebnis,
                    [$this, 'archivierungsVergleich'] // Vergleichs-Callback
                );

            // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
            if(empty($gesamtErgebnis))
                return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl);
        }



        // RESTLICHE INFO-FILTER:

        foreach($allParams as $infoName => $infoParam) {

            if(empty($infoParam))
                continue;

            $infoArray = explode(";", $infoParam);
            $infoName = MysqlEscapeHelper::escape($infoName, false);

            $infoWerte = "";
            $isFirst = true;

            foreach($infoArray as $infoWert) {
                $infoWert = trim($infoWert);

                if(empty($infoWert))
                    continue;

                $infoWert = MysqlEscapeHelper::escape($infoWert, true);

                if($isFirst) {
                    $infoWerte = "LOWER('%$infoWert%')";
                    $isFirst = false;
                }
                else
                    $infoWerte .= " OR LOWER(i.wert) LIKE LOWER('%$infoWert%')";
            }

            if( !empty($infoWerte)) {

                $sql =
                    "SELECT a.* " .
                    "FROM Archivierung a " .
                    "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
                    "LEFT JOIN Information i ON ai.information_id = i.id " .
                    "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
                    "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
                    "WHERE ( LOWER(i.wert) LIKE $infoWerte ) AND i.name = '$infoName' " .
                    "ORDER BY a.erstelldatum DESC"
                ;
                $query = $em->createNativeQuery($sql, $rsm);
                $infoErgebnis = $query->getResult();

                // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
                if(empty($infoErgebnis))
                    return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl);

                else
                    // Schnittmenge bilden
                    $gesamtErgebnis = array_uintersect(
                        $gesamtErgebnis,
                        $infoErgebnis,
                        [$this, 'archivierungsVergleich'] // Vergleichs-Callback
                    );

                // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
                if(empty($gesamtErgebnis))
                    return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl);
            }
        }


        // Gesamtergebnis der Suche returnen:
        return $this->createSuchResponse($gesamtErgebnis, $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl);
    }



    /**
     * @Route("/ajaxVorschlaege/", name="_ajaxVorschlaege")
     */
    public function ajaxVorschlaegeAction(Request $request) {

        if($request->isXmlHttpRequest()) {
            $infoName = $request->request->get('infoName');
            $infoWert = $request->request->get('infoWert');
            $infoPicked = $request->request->get('infoPicked'); // werte die bereits in der Auswahl enthalten sind.

            $pickedArray = explode(";", $infoPicked);

            $ergebnis = $this->vorschlaegeSuche($infoName, $infoWert, $pickedArray);

            return new JsonResponse([
                    'vorschlaege' => $ergebnis
                ]);
        }

        return $this->redirectToRoute("_index");
    }

    /**
     * Liefert passend zum InfoNamen und InfoWert passende Vorschaege zur Verfollstaendigung.
     * Werte die bereits in $pickedArray stehen werden ignoriert und stattdessen andere zurückgegeben.
     * maximal 5 Vorschlaege werden gesucht.
      */
    private function vorschlaegeSuche($infoName, $infoWert, $pickedArray) {
        $pickedWerte = "";
        $isFirst = true;

        foreach($pickedArray as $pickedWert) {
            $pickedWert = trim($pickedWert);

            if(empty($pickedWert))
                continue;

            $pickedWert = MysqlEscapeHelper::escape($pickedWert, false);

            if($isFirst) {
                $pickedWerte = "'$pickedWert'";
                $isFirst = false;
            }
            else
                $pickedWerte .= ", '$pickedWert'";
        }

        $sqlPicked = "";
        if( !empty($pickedWerte) ) {
            $sqlPicked = "AND i.wert NOT IN($pickedWerte) ";
        }

        $sqlWert = "";
        if( !empty($infoWert) ) {

            $infoWert = MysqlEscapeHelper::escape($infoWert, true);
            $sqlWert = "LOWER(i.wert) LIKE '%$infoWert%' AND ";
        }

        $sqlName = "i.name != 'Tags' ";
        if($infoName != "Freitext") {

            $infoName = MysqlEscapeHelper::escape($infoName, false);
            $sqlName = "i.name = '$infoName' ";
        }

        $em = $this->getDoctrine()->getManager();

        $sql =
            "SELECT i.wert, COUNT(*) AS haeufigkeit " .
            "FROM Information i " .
            "INNER JOIN Archivierung_Information ai ON i.id = ai.information_id " .
            "WHERE " .
            $sqlWert .
            $sqlName .
            $sqlPicked .
            "GROUP BY i.wert " .
            "ORDER BY haeufigkeit DESC " .
            "LIMIT 5"
        ;
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $ergebnis = $stmt->fetchAll();

        return $ergebnis;
    }


    /**
     * @param $a1 Archivierung
     * @param $a2 Archivierung
     * @return int 0 wenn die beiden Archivierungen die Gleiche ID haben. 1 oder -1 wenn die beiden Archivierungen nicht die gleiche ID haben.
     */
    private function archivierungsVergleich($a1, $a2) {
        $id1 = $a1->getId();
        $id2 = $a2->getId();
        if($id1 == $id2)
            return 0;
        elseif($id1 > $id2)
            return 1;
        else
            return -1;
    }

    /**
     * @param $archivierungen array mit Archivierungen
     * @param $minDBJahr int
     * @param $maxDBJahr int
     * @param $vorschlaege array
     * @param $auswahl array
     * @return Response
     */
    private function createSuchResponse($archivierungen, $minDBJahr, $maxDBJahr, $vorschlaege, $auswahl) {

        foreach($archivierungen as $key => $archivierung) {
            $dateiHash = $archivierung->getDateiHash();

            $links = HashHelper::dateiHashToURL($dateiHash);    // Pfade zu Bildern
            $archivierungen[$key]->links = $links;
        }

        return $this->render('default/index.html.twig', [
            "archivierungen" => $archivierungen,
            "minDBJahr" => $minDBJahr,
            "maxDBJahr" => $maxDBJahr,
            "vorschlaege" => $vorschlaege,
            "auswahl" => $auswahl
        ]);
    }
}
