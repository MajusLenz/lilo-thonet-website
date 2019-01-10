<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Helper\HashHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class DetailController extends Controller
{
    /**
     * @Route("/Detail/{slug}", name="_detail")
     */
    public function indexAction(Request $request, $slug)
    {

        if(!is_numeric($slug) || $slug < 1) {
            throw $this->createNotFoundException('Geben Sie ein gültiges Format für IDs ein!');
        }

        $em = $this->getDoctrine()->getManager();
        $archivierung = $em->getRepository('AppBundle:Archivierung')->find($slug);

        if($archivierung === null) {
            throw $this->createNotFoundException('Eine Archivierung mit dieser ID existiert nicht!');
        }

        $links = HashHelper::dateiHashToURL( $archivierung->getDateiHash() );   // Pfade zu den Bildern
        $archivierung->links = $links;


        // Für Twig die Jahre aufbereiten:
        $jahre = $archivierung->getJahre();

        if(count($jahre) === 0 ) {
            $archivierung->jahrString = "";
        }
        if( count($jahre) === 1 ) {
            $archivierung->jahrString = "". $jahre[0]->getWert();
        }
        else{
            $jahreArray = array();
            foreach($jahre as $jahr) {
                $jahreArray[] = $jahr->getWert();
            }

            sort($jahreArray, SORT_NUMERIC);
            $erstesJahr = $jahreArray[0];
            $letztesJahr = array_pop($jahreArray);

            $archivierung->jahrString = "$erstesJahr - $letztesJahr";
        }


        // Für Twig die Infos aufbereiten:
        $infosArray = array();
        foreach($archivierung->getInfos(null) as $info) {
            $name = $info->getName();
            $wert = $info->getWert();

            if( !array_key_exists($name, $infosArray) )
                $infosArray[$name] = "$wert";

            else
                $infosArray[$name] .= ", $wert";
        }
        $archivierung->infosArray = $infosArray;


        // Verknüpfte Objekte laden:
        $tags = $archivierung->getInfos("Tags");
        $verknuepfteObjekte = array();

        if(!empty($tags)) {

            // Der richtige Weg, wenn Doctrine nicht ekelhaft verbuggt wäre:
            //$verknuepfteObjekte = $archivierung = $em->getRepository('AppBundle:Archivierung')->findBy(array("infos" => 1));

            // Die Alternative:
            $tagIDs = "";
            $firstOne = true;
            foreach($tags as $tag) {
                $tagID = $tag->getId();

                if($firstOne) {
                    $tagIDs .= "$tagID";
                    $firstOne = false;
                }
                else{
                    $tagIDs .= ", $tagID";
                }
            }
            $sql = "SELECT a.* " .
                "FROM Archivierung a " .
                "INNER JOIN Archivierung_Information ai ON a.id = ai.archivierung_id " .
                "INNER JOIN Information i ON ai.information_id = i.id " .
                "WHERE i.id IN ($tagIDs)"
            ;
            $rsm = new ResultSetMappingBuilder($em);
            $rsm->addRootEntityFromClassMetadata('AppBundle:Archivierung', 'a');
            $query = $em->createNativeQuery($sql, $rsm);
            $verknuepfteObjekte = $query->getResult();


            foreach($verknuepfteObjekte as $key => $vObjekt) {
                // Die Haupt-Archivierung aussortieren, damit sie nicht mit sich selbst vernuepft ist.
                if($vObjekt == $archivierung) {
                    unset($verknuepfteObjekte[$key]);
                }
                else{
                    $vLinks = HashHelper::dateiHashToURL( $vObjekt->getDateiHash() );   // Pfade zu Bildern
                    $verknuepfteObjekte[$key]->links = $vLinks;
                }
            }

        }


        return $this->render('detail/detail.html.twig', [
            "archivierung" => $archivierung,
            "verknuepfteObjekte" => $verknuepfteObjekte
        ]);
    }


}
