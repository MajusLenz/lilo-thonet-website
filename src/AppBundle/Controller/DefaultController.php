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



        // Freitext-Suche
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE ( ( LOWER(i.wert) LIKE LOWER('%illustration%') AND i.name != 'Tags' ) OR LOWER(j.wert) LIKE LOWER('%illustration%') )"
        ;

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('AppBundle:Archivierung', 'a');
        $query = $em->createNativeQuery($sql, $rsm);
        $archivierungenFreitext = $query->getResult();


        // Filter-Suche
        $sql =
            "SELECT a.* " .
            "FROM Archivierung a " .
            "LEFT JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
            "LEFT JOIN Information i ON ai.information_id = i.id " .
            "LEFT JOIN Archivierung_Jahr aj ON a.id = aj.archivierung_id " .
            "LEFT JOIN Jahr j ON aj.jahr_id = j.id " .
            "WHERE ( j.wert IN(1957) )"
        ;

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('AppBundle:Archivierung', 'a');
        $query = $em->createNativeQuery($sql, $rsm);
        $archivierungenFilter = $query->getResult();


        $archivierungen = array_uintersect($archivierungenFreitext, $archivierungenFilter, function($a1, $a2){
            if($a1->getId() == $a2->getId())
                return 0;
            else
                return 1;
        });


        $archivierung = $archivierungenFreitext;


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
