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

        $em = $this->getDoctrine()->getManager();
        $test = $em->getRepository('AppBundle:Archivierung')->find(2);

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
        if($em->getRepository('AppBundle:Archivierung')->findOneByDateiname("dateiname3.jpg") === null) {
            $archivierung->setDateiname("dateiname3.jpg");
        }
        else{
            throw new Exception("Dateiname schon belegt!");
        }

        $dateiHash = md5("dateiname3.jpg");
        if($em->getRepository('AppBundle:Archivierung')->findOneByDateiHash($dateiHash) === null) {
            $archivierung->setDateiHash($dateiHash);
        }
        else{
            throw new Exception("Dateihash schon belegt!");
        }

        $archivierung->setDateinameAlt("dateinameAlt3.jpg");

        $archivierung->setArchivierungsArt("grafik");

        // ASSOZIATIONEN:

        //Infos:
        $infosArray = array(
            array("Titel", "Titel1"),
            array("Titel", "Titel2"),
            array("Auftraggeber", "Auftraggeber1"),
            array("Auftraggeber", "Auftraggeber2"),
            array("Objektkategorie", "Objektkategorie1"),
            array("Objektkategorie", "Objektkategorie2"),
            array("Gestaltung", "Gestaltung1"),
            array("Gestaltung", "Gestaltung2"),
            array("Fotografie", "Fotografie1"),
            array("Fotografie", "Fotografie2"),
            array("Druck", "Druck1"),
            array("Druck", "Druck2"),
            array("Autor", "Autor1"),
            array("Autor", "Autor2"),
            array("Maße", "Maße1"),
            array("Maße", "Maße2"),
            array("Umfang", "Umfang1"),
            array("Umfang", "Umfang2"),
            array("Material", "Material1"),
            array("Material", "Material2"),
            array("Produktionsverfahren", "Produktionsverfahren1"),
            array("Produktionsverfahren", "Produktionsverfahren2"),
            array("Sprache", "Sprache1"),
            array("Sprache", "Sprache2"),
            array("Schrift", "Schrift1"),
            array("Schrift", "Schrift2"),
            array("Farbe", "Farbe1"),
            array("Farbe", "Farbe2"),
            array("Bilddarstellung", "Bilddarstellung1"),
            array("Bilddarstellung", "Bilddarstellung2"),
            array("Hinweis", "Hinweis1"),
            array("Hinweis", "Hinweis2"),
            array("Produktkategorie", "Produktkategorie1"),
            array("Produktkategorie", "Produktkategorie2"),
            array("Text", "Text1"),
            array("Text", "Text2"),
            array("Ereignisse", "Ereignisse1"),
            array("Ereignisse", "Ereignisse2"),
            array("Archiv", "Archiv1"),
            array("Archiv", "Archiv2"),
        );

        foreach ($infosArray as $infosEintrag) {
            $infoName = $infosEintrag[0];
            $infoWert = $infosEintrag[1];
            $info = $em->getRepository('AppBundle:Information')->findOneBy(array('name' => $infoName, 'wert' => $infoWert));

            if($info === null) {
                $info = new Entity\Information();
                $info->setName($infoName);
                $info->setWert($infoWert);
            }
            $archivierung->addInfo($info);
        }

        //Jahre:
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
        }


        // Referenzen:
        $referenzDateien = array("dateiname.jpg","dateiname2.jpg");
        foreach ($referenzDateien as $referenzDatei) {
            $referenz = $em->getRepository('AppBundle:Archivierung')->findOneByDateiname($referenzDatei);

            if($referenz === null) {
                throw new Exception("Archivierung mit Dateiname $referenz nicht gefunden!");
            }
            else{
                $archivierung->addReferenzen($referenz);
            }
        }


        //Persistieren:
        $em->persist($archivierung);
        $em->flush();


        dump($archivierung);

        return $this->render('test/test.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }
}
