<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity;

class TestController extends Controller
{
    /**
     * @Route("/test", name="test")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('test/test.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/newArchivierung", name="new_Archivierung")
     */
    public function newArchivierungAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $archivierung = new Entity\Archivierung();

        // Einfache Attribute
        if($em->getRepository('AppBundle:Archivierung')->findOneByDateiname("dateiname.jpg") === null) {
            $archivierung->setDateiname("dateiname.jpg");
        }
        else{
            throw new Exception("Dateiname schon belegt!");
        }

        $archivierung->setTitel("titel");

        $archivierung->setDateinameAlt("dateinameAlt.jpg");

        $dateiHash = md5("dateiname.jpg");
        if($em->getRepository('AppBundle:Archivierung')->findOneByDateiHash($dateiHash) === null) {
            $archivierung->setDateiHash($dateiHash);
        }
        else{
            throw new Exception("Dateihash schon belegt!");
        }


        // ASSOZIATIONEN M2O:

        $breite = $em->getRepository('AppBundle:Breite')->findOneByWert(300);
        if($breite === null) {
            $breite = new Entity\Breite();
            $breite->setWert(300);
        }
        $archivierung->setBreite($breite);
        //$breite->addArchivierungen($archivierung);
        //$em->persist($breite);


        $hoehe = $em->getRepository('AppBundle:Hoehe')->findOneByWert(300);
        if($hoehe === null) {
            $hoehe = new Entity\Hoehe();
            $hoehe->setWert(300);
        }
        $archivierung->setHoehe($hoehe);
        //$hoehe->addArchivierungen($archivierung);
        //$em->persist($hoehe);


        $herkunftsarchiv = $em->getRepository('AppBundle:Herkunftsarchiv')->findOneByName("Testarchiv");
        if($herkunftsarchiv === null) {
            $herkunftsarchiv = new Entity\Herkunftsarchiv();
            $herkunftsarchiv->setName("Testarchiv");
        }
        $archivierung->setHerkunftsarchiv($herkunftsarchiv);
        //$herkunftsarchiv->addArchivierungen($archivierung);
        //$em->persist($herkunftsarchiv);


        $firma = $em->getRepository('AppBundle:Firma')->findOneByName("Testfirma");
        if($firma === null) {
            $firma = new Entity\Firma();
            $firma->setName("Testfirma");
        }
        $archivierung->setFirma($firma);
        //$firma->addArchivierungen($archivierung);
        //$em->persist($firma);


        $entstehungsort = $em->getRepository('AppBundle:Ort')->findOneBy(
            array('stadt' => "Teststadt", 'land' => 'Testland')
        );
        if($entstehungsort === null) {
            $entstehungsort = new Entity\Ort();
            $entstehungsort->setStadt("Teststadt");
            $entstehungsort->setLand("Testland");
        }
        $archivierung->setEntstehungsort($entstehungsort);
        //$entstehungsort->addArchivierungen($archivierung);
        $em->persist($entstehungsort);

        $em->flush(); // Ort persistieren, da nach ihm im weiteren gesucht werden könnte.


        // ASSOZIATIONEN M2M:

        $kategorieNamen = array("Testkategorie1", "Testkategorie2");

        foreach ($kategorieNamen as $kategorieName) {
            $kategorie = $em->getRepository('AppBundle:Kategorie')->findOneByName($kategorieName);
            if($kategorie === null) {
                $kategorie = new Entity\Kategorie();
                $kategorie->setName($kategorieName);
            }
            $archivierung->addKategorien($kategorie);
            //$kategorie->addArchivierungen($archivierung);
            //$em->persist($kategorie);
        }


        $farbeNamen = array("Testfarbe1", "Testfarbe2");

        foreach ($farbeNamen as $farbeName) {
            $farbe = $em->getRepository('AppBundle:Farbe')->findOneByName($farbeName);
            if($farbe === null) {
                $farbe = new Entity\Farbe();
                $farbe->setName($farbeName);
            }
            $archivierung->addFarben($farbe);
            //$farbe->addArchivierungen($archivierung);
            //$em->persist($farbe);
        }


        $schriftNamen = array("Testschrift1", "Testschrift2");

        foreach ($schriftNamen as $schriftName) {
            $schrift = $em->getRepository('AppBundle:Schrift')->findOneByName($schriftName);
            if($schrift === null) {
                $schrift = new Entity\Schrift();
                $schrift->setName($schriftName);
            }
            $archivierung->addSchriften($schrift);
            //$schrift->addArchivierungen($archivierung);
            //$em->persist($schrift);
        }


        $hinweisNamen = array("Achtung Hässlich!", "supi schön <3");

        foreach ($hinweisNamen as $hinweisName) {
            $hinweis = $em->getRepository('AppBundle:Hinweis')->findOneByName($hinweisName);
            if($hinweis === null) {
                $hinweis = new Entity\Hinweis();
                $hinweis->setName($hinweisName);
            }
            $archivierung->addHinweise($hinweis);
            //$hinweis->addArchivierungen($archivierung);
            //$em->persist($hinweis);
        }


        $jahrString = "1997-2001";
        $jahre = explode("-", $jahrString); // String splitten

        foreach($jahre as $index=>$jahr) {
            $jahre[$index] = (int)$jahr;    // string in int konvertieren
        }

        if(sizeof($jahre) === 1) {
            $minJahr = $jahre[0];
            $maxJahr = $jahre[0];
        }
        elseif(sizeof($jahre) === 2) {
            $minJahr = $jahre[0];
            $maxJahr = $jahre[1];
        }
        else{ throw new Exception("Format von Jahr ungültig: $jahrString"); }

        for ($i = $minJahr; $i <= $maxJahr; $i++) {
            $jahr = $em->getRepository('AppBundle:Jahr')->findOneByWert($i);
            if($jahr === null) {
                $jahr = new Entity\Jahr();
                $jahr->setWert($i);
            }
            $archivierung->addJahre($jahr);
            //$jahr->addArchivierungen($archivierung);
            //$em->persist($jahr);
        }


        ///////////// TODO    $gestaltungen


        //für jeden ort in gestaltungen mache....









        // Referenzen:




        //Persistieren:
        $em->persist($archivierung);
        $em->flush();


        dump($archivierung);

        return $this->render('test/test.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }
}
