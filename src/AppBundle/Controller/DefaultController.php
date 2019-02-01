<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Archivierung;
use AppBundle\Helper\MysqlEscapeHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Helper\HashHelper;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="_index")
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $archivierungen = $em->getRepository('AppBundle:Archivierung')->findBy(array(), array('erstelldatum' => 'ASC'));

        $sql = ""."SELECT MIN(wert) AS 'min', MAX(wert) AS 'max' FROM Jahr";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $stmtResult = $stmt->fetchAll();

        $minDBJahr = $stmtResult[0]["min"];   // kleinstes Jahr in der DB
        $maxDBJahr = $stmtResult[0]["max"];   // groesstes Jahr in der DB


        return $this->createSuchResponse($archivierungen, $minDBJahr, $maxDBJahr);
    }


    /**
     * @Route("/DasProjekt/", name="_dasProjekt")
     */
    public function dasProjektAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/dasProjekt.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/ArbeitenImKontext/", name="_arbeitenImKontext")
     */
    public function arbeitenImKontextAction(Request $request)
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
    public function kontaktAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/kontakt.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }


    /**
     * @Route("/suche/", name="_suchetest")
     */
    public function sucheAction(Request $request)
    {

//        // Verknüpfte Objekte laden:
//        $tags = $archivierung->getInfos("Tags");
//        $verknuepfteObjekte = array();
//
//        if(!empty($tags)) {
//
//            // Der richtige Weg, wenn Doctrine nicht ekelhaft verbuggt wäre:
//            //$verknuepfteObjekte = $archivierung = $em->getRepository('AppBundle:Archivierung')->findBy(array("infos" => 1));
//
//            // Die Alternative:
//            $tagIDs = "";
//            $firstOne = true;
//            foreach($tags as $tag) {
//                $tagID = $tag->getId();
//
//                if($firstOne) {
//                    $tagIDs = "$tagID";
//                    $firstOne = false;
//                }
//                else{
//                    $tagIDs .= ", $tagID";
//                }
//            }


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


        // FREITEXT-SUCHE:

        $freitextParam = trim($allParams["Freitext"]);
        unset($allParams["Freitext"]);
        $freitextString = MysqlEscapeHelper::escape($freitextParam);

        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE ( ( LOWER(i.wert) LIKE LOWER('%$freitextString%') AND i.name != 'Tags' ) OR LOWER(j.wert) LIKE LOWER('%$freitextString%') )"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $freitextErgebnis = $query->getResult();


        // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
        if(empty($freitextErgebnis))
            return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr);

        else
            $gesamtErgebnis = $freitextErgebnis;



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
                "WHERE j.wert IN($jahrString)"
            ;
            $query = $em->createNativeQuery($sql, $rsm);
            $jahrErgebnis = $query->getResult();

            // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
            if(empty($jahrErgebnis))
                return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr);

            else
                // Schnittmenge bilden
                $gesamtErgebnis = array_uintersect(
                    $freitextErgebnis,
                    $jahrErgebnis,
                    [$this, 'archivierungsVergleich'] // Vergleichs-Callback
                );

            // wenn jetzt die Ergebnismenge schon leer ist, die anderen Filter ignorieren und Response mit leerer Menge returnen
            if(empty($gesamtErgebnis))
                return $this->createSuchResponse(array(), $minDBJahr, $maxDBJahr);
        }



        // RESTLICHE INFO-FILTER:

        foreach($allParams as $infoParam) {

            if(empty($infoParam))
                continue;

            $infoArray = explode(";", $infoParam);

            foreach($infoArray as $info) {
                $info = trim($info);

                if(empty($info))
                    continue;

                
            }
        }





        // Titel-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Titel')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterTitel = $query->getResult();

        // Auftraggeber-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Auftraggeber')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterAuftraggeber = $query->getResult();

        // Objektkategorie-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Möbelkatalog') AND i.name = 'Objektkategorie')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $titelObjektkategorie = $query->getResult();

        // Gestaltung-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Gestaltung')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterGestaltung = $query->getResult();

        // Fotografie-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Fotografie')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterFotografie = $query->getResult();

        // Druckerei-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Druckerei')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterDruckerei = $query->getResult();

        // Autor-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Autor')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterAutor = $query->getResult();

        // Material-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Material')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterMaterial = $query->getResult();

        // Produktionsverfahren-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Produktionsverfahren')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterProduktionsverfahren = $query->getResult();

        // Sprache-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Sprache')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterSprache = $query->getResult();

        // Schrift-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Schrift')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterSchrift = $query->getResult();

        // Farbe-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Farbe')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterFarbe = $query->getResult();

        // Bilddarstellung-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Bilddarstellung')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterBilddarstellung = $query->getResult();

        // Hinweis-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Hinweis')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterHinweis = $query->getResult();

        // Produktkategorie-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Produktkategorie')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterProduktkategorie = $query->getResult();

        // Möbel-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Moebel')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterMoebel = $query->getResult();

        // Text-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Text')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterText = $query->getResult();

        // Archiv-Filter:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE (i.wert IN('Thonet 157') AND i.name = 'Archiv')"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $filterArchiv = $query->getResult();


        // Schnittmenge aller Ergebnismengen:
        $archivierungen = array_uintersect(
            $freitextErgebnis,
            $jahrErgebnis,
//            $filterTitel,
//            $filterAuftraggeber,
//            $titelObjektkategorie,
//            $filterGestaltung,
//            $filterFotografie,
//            $filterDruckerei,
//            $filterAutor,
//            $filterMaterial,
//            $filterProduktionsverfahren,
//            $filterSprache,
//            $filterSchrift,
//            $filterFarbe,
//            $filterBilddarstellung,
//            $filterHinweis,
//            $filterProduktkategorie,
//            $filterMoebel,
//            $filterText,
//            $filterArchiv,

            [$this, 'archivierungsVergleich']
        );


        return $this->createSuchResponse($archivierungen, $minDBJahr, $maxDBJahr);
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
     * @return Response
     */
    private function createSuchResponse($archivierungen, $minDBJahr, $maxDBJahr) {

        foreach($archivierungen as $key => $archivierung) {
            $dateiHash = $archivierung->getDateiHash();

            $links = HashHelper::dateiHashToURL($dateiHash);    // Pfade zu Bildern
            $archivierungen[$key]->links = $links;
        }

        return $this->render('default/index.html.twig', [
            "archivierungen" => $archivierungen,
            "minDBJahr" => $minDBJahr,
            "maxDBJahr" => $maxDBJahr
        ]);
    }
}
