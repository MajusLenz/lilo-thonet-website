<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Archivierung;
use AppBundle\Entity\ArchivierungsUpload;
use AppBundle\Entity\Information;
use AppBundle\Entity\Jahr;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

            $em = $this->getDoctrine()->getManager();

            $file = $upload->getCsv();
            $errorList = array();

            $data = $this->csv_to_array($file, "~");

            // Jede Zeile der Tabelle bearbeiten
            foreach($data as $rowCount => $row) {
                $zeilenNr = $rowCount + 2;

                $archivierungsID = trim($row["Archivierungs-ID"]);
                unset($row["Archivierungs-ID"]);

                //bestehende Archivierung bearbeiten
                if($archivierungsID) {
                    $archivierung = $em->getRepository('AppBundle:Archivierung')->find($archivierungsID);

                    // Fehler wenn Archivierungs-ID zu keiner Archivierung führt
                    if($archivierung === null) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Bearbeiten der Archivierung nicht möglich. Archivierung mit ID "
                            . $archivierungsID . " nicht gefunden! Zeile übersprungen!"
                        );

                        $em->detach($archivierung);
                        continue; // gesamte Zeile überspringen
                    }
                }
                //oder neue archivierung erstellen
                else{
                    $archivierung = new Archivierung();
                    $archivierung->setArchivierungsArt("Grafik");
                }


                $dateiname = trim($row["Dateiname"]);
                unset($row["Dateiname"]);

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


                $dateinameAlt = trim($row["Dateiname (alt)"]);
                unset($row["Dateiname (alt)"]);

                if($dateinameAlt)
                    $archivierung->setDateinameAlt($dateinameAlt);


                $jahre = trim($row["Jahr"]);
                unset($row["Jahr"]);

                if($jahre) {
                    $minJahr = 0;
                    $maxJahr = 0;
                    $jahreArray = explode("-", $jahre);

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

                    if (count($jahre) === 1) {
                        $minJahr = trim($jahre[0]);
                        $maxJahr = $minJahr;
                    } elseif (count($jahre) === 2) {
                        $minJahr = trim($jahre[0]);
                        $maxJahr = trim($jahre[1]);
                    }

                    if($minJahr > $maxJahr) {
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

                $referenzen = trim($row["Verknuepfte Objekte"]);
                unset($row["Verknuepfte Objekte"]);

                if($referenzen) {
                    $referenzenArray = explode(";", $referenzen);

                    foreach ($referenzenArray as $referenzString) {

                        $referenzString = trim($referenzString);
                        if ($referenzString) {
                            $referenzArchivierung = $em->getRepository('AppBundle:Archivierung')->findOneByDateiname($referenzString);

                            // Fehler wenn Referenz in Datenbank nicht existiert
                            if($referenzArchivierung === null) {
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

                // Alle weiteren Attribute werden in der Entity "Information" gespeichert:
                foreach($row as $key => $values) {
                    $keyString = trim($key);
                    $valuesString = trim($values);

                    if($keyString && $valuesString) {
                        $valuesArray = explode(";", $valuesString);

                        foreach ($valuesArray as $value) {
                            $value = trim($value);

                            if($value) {
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

                $dateiHash = md5($dateiname);
                ///////////////////////////////////
                // TODO Logik für Bilder einfügen
                ///////////////////////////////////
                $archivierung->setDateiHash($dateiHash);

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
            $errorList = array("Es gab keine Fehler. Alles wurde erfolgreich hochgeladen!");
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
