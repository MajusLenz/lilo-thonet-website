<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Helper\HashHelper;

class FavoritenController extends Controller
{
    /**
     * @Route("/Favoriten/", name="_favoriten")
     */
    public function indexAction(Request $request)
    {
        $favoritenString = $request->cookies->get("favoriten");
        $favoriten = explode("-", $favoritenString);

        $em = $this->getDoctrine()->getManager();
        $archivierungen = $em->getRepository('AppBundle:Archivierung')->findById($favoriten, array('erstelldatum' => 'ASC'));

        foreach($archivierungen as $key => $archivierung) {
            $dateiHash = $archivierung->getDateiHash();

            $links = HashHelper::dateiHashToURL($dateiHash);    // Pfade zu Bildern
            $archivierungen[$key]->links = $links;
        }
        
        return $this->render('favoriten/favoriten.html.twig', [
            "archivierungen" => $archivierungen
        ]);
    }

}

