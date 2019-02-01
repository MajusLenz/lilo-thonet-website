<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Helper\HashHelper;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="_index")
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $archivierungen = $em->getRepository('AppBundle:Archivierung')->findBy(array(), array('erstelldatum' => 'ASC'));

        foreach($archivierungen as $key => $archivierung) {
            $dateiHash = $archivierung->getDateiHash();

            $links = HashHelper::dateiHashToURL($dateiHash);    // Pfade zu Bildern
            $archivierungen[$key]->links = $links;
        }
        
        return $this->render('default/index.html.twig', [
            "archivierungen" => $archivierungen
        ]);
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

        $em = $this->getDoctrine()->getManager();



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
//                    $tagIDs .= "$tagID";
//                    $firstOne = false;
//                }
//                else{
//                    $tagIDs .= ", $tagID";
//                }
//            }



        $allParams = $request->query->all();



        $freitextParam = trim($allParams["Freitext"]);
        unset($allParams["Freitext"]);


        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('AppBundle:Archivierung', 'a');

        // Freitext-Suche:
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE ( ( LOWER(i.wert) LIKE LOWER('%papier%') AND i.name != 'Tags' ) OR LOWER(j.wert) LIKE LOWER('%papier%') )"
        ;
        $query = $em->createNativeQuery($sql, $rsm);
        $freitextSuche = $query->getResult();






        $jahrParam = trim($allParams["Jahr"]);
        unset($allParams["Jahr"]);

        $minJahr = 0;
        $maxJahr = 0;
        $jahreArray = explode(";", $jahrParam);

        // Fehler wenn Jahre in falschem Format angegeben
        if (count($jahreArray) > 2) {
            return $this->getResponse()->setStatusCode('422');
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
            return $this->getResponse()->setStatusCode('422');
        }

        $jahrString = "$minJahr";

        for ($i = $minJahr+1; $i <= $maxJahr; $i++) {
            $jahrString = $jahrString . ", $i";
        }

        // Jahr-Filter:
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
        $filterJahr = $query->getResult();













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
            $freitextSuche,
            $filterJahr,
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

            function($a1, $a2) {
                $id1 = $a1->getId();
                $id2 = $a2->getId();
                if($id1 == $id2)
                    return 0;
                elseif($id1 > $id2)
                    return 1;
                else
                    return -1;
            }
        );


        //$archivierungen = $archivierungenFilter;

        foreach($archivierungen as $key => $archivierung) {
            $dateiHash = $archivierung->getDateiHash();

            $links = HashHelper::dateiHashToURL($dateiHash);    // Pfade zu Bildern
            $archivierungen[$key]->links = $links;
        }

        return $this->render('default/index.html.twig', [
            "archivierungen" => $archivierungen
        ]);
    }
}
