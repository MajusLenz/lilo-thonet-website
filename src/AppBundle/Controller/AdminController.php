<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Archivierung;
use AppBundle\Entity\ArchivierungsUpload;
use AppBundle\Entity\Information;
use AppBundle\Entity\Jahr;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction(Request $request)
    {
        $upload = new ArchivierungsUpload();

        $form = $this->createFormBuilder($upload)
            ->add('csv', FileType::class, array('label' => 'Archivierungsliste im CSV-Format:'))
            ->add('save', SubmitType::class, array('label' => 'Hochladen'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $fs = new Filesystem();
            $em = $this->getDoctrine()->getManager();
            $neueBilderOrdner = $this->getParameter('neue_bilder_ordner');
            $gespeicherteBilderOrdner = $this->getParameter('gespeicherte_bilder_ordner');

            $file = $upload->getCsv();
            $errorList = array();

            $data = $this->csv_to_array($file, "~");

            // Jede Zeile der Tabelle bearbeiten
            foreach($data as $rowCount => $row) {
                $zeilenNr = $rowCount + 2;
                $isEdit = false;
                $delete = "DELETE";

                $archivierungsID = trim($row["Archivierungs-ID"]);
                unset($row["Archivierungs-ID"]);

                //bestehende Archivierung bearbeiten
                if($archivierungsID) {
                    if( !is_numeric($archivierungsID) ) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Bearbeiten der Archivierung nicht möglich. ArchivierungID "
                            . $archivierungsID . " ist keine Zahl! Zeile übersprungen!"
                        );

                        continue; // gesamte Zeile überspringen
                    }

                    $archivierung = $em->getRepository('AppBundle:Archivierung')->find($archivierungsID);

                    // Fehler wenn Archivierungs-ID zu keiner Archivierung führt
                    if($archivierung === null) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Bearbeiten der Archivierung nicht möglich. Archivierung mit ID "
                            . $archivierungsID . " nicht gefunden! Zeile übersprungen!"
                        );

                        continue; // gesamte Zeile überspringen
                    }
                    $archivierung->setLetzteBearbeitung(new DateTime("now"));
                    $isEdit = true;
                }

                //oder neue archivierung erstellen
                else{
                    $archivierung = new Archivierung();
                    $archivierung->setArchivierungsArt("Grafik");
                }


                $dateiname = trim(utf8_encode($row["Dateiname"]));
                unset($row["Dateiname"]);

                // Fehler wenn Dateiname == DELETE
                if($dateiname === $delete) {
                    array_push(
                        $errorList,
                        "Zeile $zeilenNr: Pflichtfeld 'Dateiname' darf nicht durch $delete gelöscht werden, "
                        . "da es nie leer sein darf! Zeile übersprungen!"
                    );

                    $em->detach($archivierung);
                    continue; // gesamte Zeile überspringen
                }

                // Dateinamen für neue Archivierung
                if($isEdit === false) {

                    // Fehler wenn Dateiname nicht vorhanden
                    if(empty($dateiname)) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Pflichtfeld 'Dateiname' ist leer! Zeile übersprungen!"
                        );

                        $em->detach($archivierung);
                        continue; // gesamte Zeile überspringen
                    }

                    // Fehler wenn Dateiname nicht einzigartig
                    $doppelgaenger = $em->getRepository('AppBundle:Archivierung')->findOneByDateiname($dateiname);
                    if($doppelgaenger !== null){
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Dateiname '" . $dateiname
                            . "' ist bereits in der Datenbank enthalten! Zeile übersprungen!"
                        );

                        $em->detach($archivierung);
                        continue; // gesamte Zeile überspringen
                    }

                    $archivierung->setDateiname($dateiname);
                }

                // vorhanden Dateinamen bearbeiten
                else{
                    // wenn was drin steht
                    if( !empty($dateiname) ) {

                        // Fehler wenn Dateiname nicht einzigartig
                        $doppelgaenger = $em->getRepository('AppBundle:Archivierung')->findOneByDateiname($dateiname);
                        if ($doppelgaenger !== null) {
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Dateiname '" . $dateiname
                                . "' ist bereits in der Datenbank enthalten! Zeile übersprungen!"
                            );

                            $em->detach($archivierung);
                            continue; // gesamte Zeile überspringen
                        }

                        $archivierung->setDateiname($dateiname);
                    }
                }


                $dateinameAlt = trim(utf8_encode($row["Dateiname (alt)"]));
                unset($row["Dateiname (alt)"]);

                if($dateinameAlt) {
                    // DateinamenAlt für neue Archivierung
                    if($isEdit === false) {
                        $archivierung->setDateinameAlt($dateinameAlt);
                    }
                    // vorhanden DateinamenAlt bearbeiten
                    else{
                        if($dateinameAlt === $delete) {
                            $archivierung->setDateinameAlt(null);
                        }
                        else{
                            $archivierung->setDateinameAlt($dateinameAlt);
                        }
                    }
                }


                $jahre = trim(utf8_encode($row["Jahr"]));
                unset($row["Jahr"]);

                if($jahre) {

                    // Bei Bearbeitung erst ALLE Jahre entfernen
                    if($isEdit) {
                        $archivierung->removeAllJahre();
                    }

                    // wenn nur gelöscht werden sollte
                    if($isEdit && $jahre === $delete) {
                        // fertig
                    }

                    // ansonsten noch neue jahre hinzufügen
                    else {
                        $minJahr = 0;
                        $maxJahr = 0;
                        $jahreArray = explode(";", $jahre);

                        // Fehler wenn Jahre in falschem Format angegeben
                        if (count($jahreArray) > 2) {
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Jahr " . $jahre
                                . " hat das falsche Format! Erlaubt: ZAHL oder ZAHL-ZAHL. Zeile übersprungen!"
                            );

                            $em->detach($archivierung);
                            continue; // gesamte Zeile überspringen
                        }

                        // ein Jahr
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
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Jahr " . $jahre
                                . " enthält eine ungültiige Zahl! Erlaubt sind nur glatte Jahreszahlen. Zeile übersprungen!"
                            );

                            $em->detach($archivierung);
                            continue; // gesamte Zeile überspringen
                        }

                        for ($i = $minJahr; $i <= $maxJahr; $i++) {
                            $jahrObjekt = $em->getRepository('AppBundle:Jahr')->findOneByWert($i);
                            if ($jahrObjekt === null) {
                                $jahrObjekt = new Jahr(intval($i));
                            }
                            $archivierung->addJahre($jahrObjekt);
                        }
                    }
                }

                $referenzen = trim(utf8_encode($row["Verknuepfte Objekte"]));
                unset($row["Verknuepfte Objekte"]);

                if($referenzen) {

                    // Bei Bearbeitung erst ALLE Referenzen entfernen
                    if($isEdit) {
                        $archivierung->removeAllReferenzen();
                    }

                    // wenn nur gelöscht werden sollte
                    if($isEdit && $referenzen === $delete) {
                        // fertig
                    }

                    // ansonsten noch neue Referenzen hinzufügen
                    else {
                        $referenzenArray = explode(";", $referenzen);

                        foreach ($referenzenArray as $referenzString) {

                            $referenzString = trim($referenzString);
                            if ($referenzString) {
                                $referenzArchivierung = $em->getRepository('AppBundle:Archivierung')->findOneByDateiname($referenzString);

                                // Fehler wenn Referenz in Datenbank nicht existiert
                                if ($referenzArchivierung === null) {
                                    array_push(
                                        $errorList,
                                        "Zeile $zeilenNr: Verknüpfung auf Dateiname " . $referenzString
                                        . " nicht möglich, da keine Archivierung mit diesem Dateinamen gefunden wurde. Zeile übersprungen!"
                                    );

                                    $em->detach($archivierung);
                                    continue 2; // gesamte Zeile überspringen
                                }

                                $archivierung->addReferenzen($referenzArchivierung);
                            }
                        }
                    }
                }

                // Alle weiteren Attribute werden in der Entity "Information" gespeichert:
                foreach($row as $key => $values) {
                    $keyString = trim(utf8_encode($key));
                    $valuesString = trim(utf8_encode($values));

                    if($keyString && $valuesString) {

                        // Bei Bearbeitung erst ALLE Infos dieser Info-Kategorie entfernen
                        if($isEdit) {
                            $archivierung->removeAllInfos($keyString);
                        }

                        // wenn nur gelöscht werden sollte
                        if($isEdit && $valuesString === $delete) {
                            // fertig
                        }

                        // ansonsten noch neue Infos hinzufügen
                        else {
                            $valuesArray = explode(";", $valuesString);

                            foreach ($valuesArray as $value) {
                                $value = trim($value);

                                if ($value) {
                                    $info = $em->getRepository('AppBundle:Information')->findOneBy(array('name' => $keyString, 'wert' => $value));

                                    if ($info === null) {
                                        $info = new Information();
                                        $info->setName($keyString);
                                        $info->setWert($value);
                                    }
                                    $archivierung->addInfo($info);
                                }
                            }
                        }
                    }
                }

                $finder = new Finder();
                $finder->files()->in($neueBilderOrdner)->name($dateiname);
                $anzahlBilder = $finder->count();

                // Fehler wenn nicht alle Bilder gefunden werden können
                if($anzahlBilder !== 3) {
                    array_push(
                        $errorList,
                        "Zeile $zeilenNr: Es können nicht alle 3 passenden Bilder zu " . $dateiname
                        . " in den Unterverzeichnissen von neue_bilder gefuden werden. Zeile übersprungen!"
                    );

                    $em->detach($archivierung);
                    continue; // gesamte Zeile überspringen
                }

                $dateiHash = md5($dateiname);
                $ersteZweiBuchstaben = substr($dateiHash, 0, 2);
                $hashPfad = $gespeicherteBilderOrdner . "/" . $ersteZweiBuchstaben;
                $hashRest = substr($dateiHash, 2);

                foreach ($finder as $bild) {

                    if( $fs->exists($hashPfad) === false ) {
                        $fs->mkdir($hashPfad);
                    }

                    $bildGroesse = $bild->getRelativePath();
                    $bildPfad = $bild->getRealPath();

                    if($bildGroesse === "bilddateien_gross") {

                        $fs->copy($bildPfad, $hashPfad . "/" . $hashRest . "_g.png", true);
                    }

                    elseif($bildGroesse === "bilddateien_mittel") {

                        $fs->copy($bildPfad, $hashPfad . "/" . $hashRest . "_m.png", true);
                    }

                    elseif($bildGroesse === "bilddateien_klein") {

                        $fs->copy($bildPfad, $hashPfad . "/" . $hashRest . "_k.png", true);
                    }
                }

                $archivierung->setDateiHash($dateiHash);

                // Archivierung speichern
                $em->persist($archivierung);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('admin_upload_result', array('errorlist' => $errorList)));
        }

        return $this->render('admin/admin.html.twig', array(
            'form' => $form->createView(),
        ));
    }



    /**
     * @Route("/admin/upload_result", name="admin_upload_result")
     */
    public function uploadAction(Request $request)
    {
        $errorList = $request->query->get('errorlist');
        if(empty($errorList)) {
            $errorList = array("Es gab keine Fehler. Alles wurde erfolgreich hochgeladen bzw. bearbeitet!");
        }
        return $this->render('admin/upload_result.html.twig', array("errorList" => $errorList));
    }



    /**
     * Thanks to jaywilliams: http://gist.github.com/385876
     */
    private function csv_to_array($filename='', $delimiter=',')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

}
