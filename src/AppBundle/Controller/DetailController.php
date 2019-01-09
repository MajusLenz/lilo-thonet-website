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

        $links = HashHelper::dateiHashToURL( $archivierung->getDateiHash() );
        $archivierung->links = $links;

        $tags = $archivierung->getInfos("Tags");

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
            // Die Haupt-Archivierung selbst aussortieren
            if($vObjekt == $archivierung) {
                unset($verknuepfteObjekte[$key]);
            }
            else{
                $vLinks = HashHelper::dateiHashToURL( $vObjekt->getDateiHash() );
                $verknuepfteObjekte[$key]->links = $vLinks;
            }
        }


        return $this->render('detail/detail.html.twig', [
            "archivierung" => $archivierung,
            "verknuepfteObjekte" => $verknuepfteObjekte
        ]);
    }


}
