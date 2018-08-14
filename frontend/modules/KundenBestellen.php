<?php
/**
 * Created by PhpStorm.
 * User: Alexander Ullmann
 */

class KundenBestellen implements IModule
{
    private $tmp;
    private $db;

    /**
     * Gibt den HTML Code für den Bestell Bereich der Kunden zurück
     * @return mixed|string
     */
    public function render()
    {
        JSController::getInstance()->loadJSFile("kunde.bestellen.js");

        $this->db = new Connection();

        if (!isset($_GET["kw"])) {
            $_GET["kw"] = date("W");
        }

        if (!isset($_GET["jahr"])) {
            $_GET["jahr"] = date("Y");
        }

        $this->tmp = new TemplateSystem();
        $this->tmp->load("content/kunde.bestellen/Bestellen.html");
        $this->tmp->setVar("DauerhaftImAngebot", $this->createDauerhaftImAngebot());
        $this->tmp->setVar("SpeiseplanTableBody", $this->getSpeiseplanTableBody($_GET["kw"], $_GET["jahr"]));
        $this->tmp->setVar("LinkKWZuruck", $this->getLinkKWZuruck());
        $this->tmp->setVar("LinkKWVor", $this->getLinkKWVor());
        $this->tmp->setVar("CurrentDate", $this->getCurrentDate());
        return $this->tmp->Template;
    }

    /**
     * Bootstrap Cards mit Artikel die dauerhaft im Angebot sind
     * @return string HTML Code
     */
    private function createDauerhaftImAngebot()
    {
        $output = "";
        $result = $this->db->Query("SELECT * FROM `tbl_artikel` WHERE `active`=1 AND `dauerhaftesAngebot`=1");

        if ($result == null) {
            $output = "<div class=\"col-md-12\">
                            <p>In der <a href='?page=speiseplan&subpage=artikel'>Artikelverwaltung</a> können Sie dauerhafte Angebote festlegen!</p>
                        </div>";
            return $output;
        }

        foreach ($result as $item) {
            $preis = number_format($item["price"], 2, ",", ".");
            $output .= "<div class=\"col-md-2\">
                                <div class=\"card border-primary\">
                                    <div class=\"card-header\">{$item["title"]}</div>
                                    <div class=\"card-body text-primary\">
                                        <h5 class=\"card-title\">Preis: {$preis}€</h5>
                                        <p class=\"card-text\">{$item["description"]}</p>
                                    </div>
                                </div>
                            </div>";
        }

        return $output;
    }

    /**
     * Gibt den Speiseplan für die Kunden Bestellen Seite
     * @param $kw
     * @param $jahr
     * @return string
     * @throws Exception
     */
    public static function getSpeiseplanTableBody($kw, $jahr)
    {
        if (!isset($kw)) {
            $kw = date("W");
        }

        if (!isset($jahr)) {
            $jahr = date("Y");
        }

        $date = self::getDateFromKWAndJahr($kw, $jahr);
        $montag = $date->format("Y-m-d 00:00:00");
        $dienstag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $mittwoch = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $donnerstag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $freitag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $samstag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $sonntag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $wochentage = array($montag, $dienstag, $mittwoch, $donnerstag, $freitag, $samstag, $sonntag);

        $auth = Auth::getInstance();
        $db = new Connection();
        $sql = "SELECT
                    tbl_speiseplan.timestamp,
                    tbl_speiseplan.id,
                    tbl_artikel.id as 'ArtikelID',
                    tbl_artikel.title,
                    tbl_artikel.price
                FROM
                    `tbl_speiseplan`
                LEFT JOIN 
                    `tbl_artikel`
                ON tbl_artikel.id = tbl_speiseplan.artikel
                WHERE
                    `timestamp` BETWEEN '$montag' AND '$sonntag'";
        $result = $db->Query($sql);

        $userName = $auth->getUser()["username"];
        $sql = "SELECT id FROM tbl_user WHERE vorname = '$userName'";
        $userID = $db->Query($sql)[0]["id"];





        if (count($result) <= 0) {
            return "<tr><td colspan='9' style='text-align: center;'>Noch kein Essen angelegt!</td></tr>";
        }
        // TODO: 24h regeln für das buchen/rückbuchen
        $output = "";
        $output .= "<td></td>";
        foreach ($wochentage as $tag) {
            $output .= "<td><div class='list-group'>";
            foreach ($result as $eintrag) {
                if ($eintrag["timestamp"] == $tag) {
                    $preis = number_format($eintrag["price"], 2, ",", ".") . "€";
                    $gebucht = "<span class=\"badge badge-primary badge-pill\"><i class=\"material-icons\" style='font-size:9px'>done</i></span>";

                    $sql = "SELECT umsatzArt FROM tbl_umsatz WHERE speiseplan = {$eintrag["id"]} ORDER BY id DESC LIMIT 1";
                    $umsatzArt = $db->Query($sql)[0]["umsatzArt"];

                    $sql = "SELECT user FROM tbl_umsatz WHERE speiseplan = {$eintrag["id"]} AND active = 1";
                    $istUser = $db->Query($sql)[0]["user"];

                    $sql = "SELECT active FROM tbl_umsatz WHERE speiseplan = {$eintrag["id"]} ORDER BY id DESC LIMIT 1";
                    $active = $db->Query($sql)[0]["active"];


                    if (isset($active)) {
                        if ($active == 1 && $userID == $istUser) {
                            if ($umsatzArt == 1) {
                                $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action d-flex justify-content-between align-items-center speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}' speiseplanid='{$eintrag["id"]}'>{$eintrag["title"]} ($preis) $gebucht</button>";
                            } else {
                                $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action d-flex justify-content-between align-items-center speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}' speiseplanid='{$eintrag["id"]}'>{$eintrag["title"]} ($preis)</button>";
                            }
                        } elseif ($active == 0 && $userID == $istUser) {
                            if ($umsatzArt == 2) {
                                $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action d-flex justify-content-between align-items-center speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}' speiseplanid='{$eintrag["id"]}'>{$eintrag["title"]} ($preis) </button>";
                            } else {
                                $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action d-flex justify-content-between align-items-center speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}' speiseplanid='{$eintrag["id"]}'>{$eintrag["title"]} ($preis) $gebucht</button>";
                            }
                        } else {
                            $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action d-flex justify-content-between align-items-center speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}' speiseplanid='{$eintrag["id"]}'>{$eintrag["title"]} ($preis) $gebucht</button>";
                        }
                    } else {
                        $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action d-flex justify-content-between align-items-center speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}' speiseplanid='{$eintrag["id"]}'>{$eintrag["title"]} ($preis)</button>";
                    }

                }
            }
            $output .= "</div></td>";
        }

        $output .= "<td></td>";
        return $output;
    }

    /**
     * Liefert DateTime Objekt mit dem Datum vom angegebenen Jahr und Kalenderwoche
     * @param $kw Kalenderwoche
     * @param $jahr Jahr
     * @return static DateTime
     */
    public static function getDateFromKWAndJahr($kw, $jahr)
    {
        $date = new DateTime();
        return $date->setISODate($jahr, $kw);
    }

    /**
     * Liefert den HTML Code mit den Details zu einem Artikel
     * @param $artikelID
     * @param $speiseplanID
     */
    public static function getArtikelModal($artikelID, $speiseplanID)
    {
        $artikelID = intval($artikelID);
        $speiseplanID = intval($speiseplanID);


        if ($artikelID <= 0 || $speiseplanID <= 0) {
            return "Fehler: Falsche ID";
        }

        $db = new Connection();
        $result = $db->Query("SELECT * FROM `tbl_artikel` WHERE `id`=$artikelID LIMIT 1");
        if (!$result) {
            return "Fehler: Falsche ID";
        }
        $artikel = $result[0];
        $preis = number_format($artikel["price"], 2, ",", ".");
        $tmpl = new TemplateSystem();
        $tmpl->load("content/kunde.bestellen/BuchenModal.html");
        $tmpl->setVar("Title", $artikel["title"]);
        $tmpl->setVar("Preis", $preis);
        $tmpl->setVar("Beschreibung", $artikel["description"]);

        $auth = Auth::getInstance();
        $userName = $auth->getUser()["username"];
        $sql = "SELECT id FROM tbl_user WHERE vorname = '$userName'";
        $userID = $db->Query($sql)[0]["id"];

        $sql = "SELECT umsatzArt FROM tbl_umsatz WHERE speiseplan = $speiseplanID ORDER BY id DESC LIMIT 1";
        $umsatzArt = $db->Query($sql)[0]["umsatzArt"];

        $sql = "SELECT user FROM tbl_umsatz WHERE speiseplan = $speiseplanID";
        $istUser = $db->Query($sql)[0]["user"];

        $sql = "SELECT active FROM tbl_umsatz WHERE speiseplan = $speiseplanID ORDER BY id DESC LIMIT 1";
        $active = $db->Query($sql)[0]["active"];


        if (isset($active)) {
            /*if ($active == 1){
                $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"stornieren($speiseplanID)\">Stornieren</button>");
            }
            elseif ($active == 0){
                $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"buchen($speiseplanID)\">Buchen</button>");
            }*/
            if ($active == 1 && $userID == $istUser) {
                if ($umsatzArt == 1) {
                    $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"stornieren($speiseplanID)\">Stornieren</button>  <button type=\"button\" class=\"btn btn-success\" onclick=\"liken($speiseplanID)\">Liken</button>");
                    //$tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"liken($speiseplanID)\">Liken</button>");
                } else {
                    $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"buchen($speiseplanID)\">Buchen</button>");
                }
            } elseif ($active == 0 && $userID == $istUser) {
                if ($umsatzArt == 2) {
                    $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"buchen($speiseplanID)\">Buchen</button>");
                } else {
                    //$tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"stornieren($speiseplanID)\">Stornieren</button>");
                    //$tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"liken($speiseplanID)\">Liken</button>");
                    $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"stornieren($speiseplanID)\">Stornieren</button>  <button type=\"button\" class=\"btn btn-success\" onclick=\"liken($speiseplanID)\">Liken</button>");

                }
            } else {
                $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"buchen($speiseplanID)\">Buchen</button>");
            }
        } else {
            $tmpl->setVar("aktionButton", "<button type=\"button\" class=\"btn btn-primary\" onclick=\"buchen($speiseplanID)\">Buchen</button>");
        }


        //$tmpl->setVar("aktionButton",)

        return $tmpl->show();
    }

    /**
     * Gibt eine Beretung für den ausgewählten Artikel --> Like
     * @param $speiseplanID
     * @return int|string
     */
    public static function artikelLiken($speiseplanID)
    {

        $db = new Connection();

        $auth = Auth::getInstance();
        $userName = $auth->getUser()["username"];

        $sql = "SELECT umsatzArt FROM tbl_umsatz WHERE speiseplan = $speiseplanID ORDER BY id DESC LIMIT 1";
        $umsatzArt = $db->Query($sql)[0]["umsatzArt"];

        $sql = "SELECT active FROM tbl_umsatz WHERE speiseplan = $speiseplanID ORDER BY id DESC LIMIT 1";
        $active = $db->Query($sql)[0]["active"];

        $sql = "SELECT artikel FROM tbl_umsatz WHERE speiseplan = $speiseplanID ORDER BY id DESC LIMIT 1";
        $artikel = $db->Query($sql)[0]["artikel"];

        $sql = "SELECT id FROM tbl_user WHERE vorname = '$userName'";
        $userID = $db->Query($sql)[0]["id"];

        $sql = "SELECT id FROM tbl_artikel_likes WHERE artikel = $artikel AND user = $userID";
        $artikelLikesID = $db->Query($sql)[0]["id"];


        if ($active == 1 && $umsatzArt == 1 ) {

            if (!isset($artikelLikesID)){
                $sql = "INSERT INTO tbl_artikel_likes (artikel, user) VALUES ($artikel, $userID)";
                $db->Query($sql);
                return 1;
            }
            else{
                return 0 . "Dieser Artikel wurde bereits bewertet!";
            }


        } else return 0 . "Nur bereits gekaufte Artikel können bewertet werden!";

    }

    /**
     * Buchung des ausgewählten Artikels, wenn der Eintrag im Speiseplan mindestens 24 Stunden in der Zukunft liegt
     * @param $speiseplanID
     * @return int|string
     */
    public static function artikelBuchen($speiseplanID)
    {
        $speiseplanID = intval($speiseplanID);
        $aktuelleZeit = strtotime(date('Y-m-d H:i:s'));


        $db = new Connection(); //Datenbank
        $auth = Auth::getInstance();

        $sql = "SELECT timestamp FROM tbl_speiseplan WHERE id = '$speiseplanID'";
        $timestamp = $db->Query($sql)[0]["timestamp"];
        $artikelzeit = strtotime($timestamp);

        // TODO: DB Abfragen um Artikel zu buchen
        /*
         * 0. 24h vorher? Verpiss dich. Return Fehlermeldung: kann nicht mehr gebucht werden
         * 1. tbl_Speiseplan abfragen welcher Artikel
         * 2. $auth Username holen und in DB nach UserID abfragen
         * 3. umsatzArt = Buchung
         * 4. Artikel ID verlinken
         * 5. Preis in Spalte rein kopieren
         * 6. Wenn kein Fehler, dann '1' zurück geben
         */

        $dif = $artikelzeit - $aktuelleZeit;
        $stunden = $dif / (60 * 60);

        if ($stunden > 24) {
            $sql = "SELECT artikel FROM tbl_speiseplan WHERE id = '$speiseplanID'";
            $artikelID = $db->Query($sql)[0]["artikel"];
            $sql = "SELECT price FROM tbl_artikel WHERE id = '$artikelID'";
            $artikelPreis = $db->Query($sql)[0]["price"];
            $userName = $auth->getUser()["username"];
            $sql = "SELECT id FROM tbl_user WHERE vorname = '$userName'";
            $userID = $db->Query($sql)[0]["id"];


            $sql = "SELECT kontostand FROM tbl_user WHERE id = $userID";
            $kontoStand = $db->Query($sql)[0]["kontostand"];

            if ($kontoStand >= $artikelPreis) {
                $neuesGuthaben = $kontoStand - $artikelPreis;
                $sql = "UPDATE tbl_user SET kontostand = $neuesGuthaben WHERE id = $userID";
                $db->Query($sql);

                $sql = "INSERT INTO tbl_umsatz (user, umsatzArt, artikel, price, speiseplan) VALUES('$userID', 1, $artikelID, $artikelPreis, $speiseplanID)";
                $db->Query($sql);
                return 1;

            } else return 0 . "Ihr Guthaben reicht nicht aus um diesen Artikel zu buchen";
        } else {
            return 0 . "Essen muss mindestens 1 Tag vorher gebucht werden!";
        }

    }

    /**
     * Stornierung des ausgewählten Artikels, wenn der Eintrag im Speiseplan mindestens 24 Stunden in der Zukunft liegt
     * @param $speiseplanID
     * @return int|string
     */
    public static function artikelStornieren($speiseplanID)
    {
        $speiseplanID = intval($speiseplanID);
        $aktuelleZeit = strtotime(date('Y-m-d H:i:s'));


        $db = new Connection(); //Datenbank
        $auth = Auth::getInstance();

        $sql = "SELECT timestamp FROM tbl_speiseplan WHERE id = '$speiseplanID'";
        $timestamp = $db->Query($sql)[0]["timestamp"];
        $artikelzeit = strtotime($timestamp);


        $dif = $artikelzeit - $aktuelleZeit;
        $stunden = $dif / (60 * 60);

        if ($stunden > 24) {
            $sql = "SELECT artikel FROM tbl_speiseplan WHERE id = '$speiseplanID'";
            $artikelID = $db->Query($sql)[0]["artikel"];
            $sql = "SELECT price FROM tbl_artikel WHERE id = '$artikelID'";
            $artikelPreis = $db->Query($sql)[0]["price"];
            $userName = $auth->getUser()["username"];
            $sql = "SELECT id FROM tbl_user WHERE vorname = '$userName'";
            $userID = $db->Query($sql)[0]["id"];


            $sql = "SELECT kontostand FROM tbl_user WHERE id = $userID";
            $kontoStand = $db->Query($sql)[0]["kontostand"];

            $sql = "SELECT user FROM tbl_umsatz WHERE speiseplan = $speiseplanID";
            $istUser = $db->Query($sql)[0]["user"];

            if ($stunden > 24 && $userID == $istUser) {
                $neuesGuthaben = $kontoStand + $artikelPreis;
                $sql = "UPDATE tbl_user SET kontostand = $neuesGuthaben WHERE id = $userID";
                $db->Query($sql);

                $sql = "UPDATE tbl_umsatz SET active = 0 WHERE speiseplan = $speiseplanID ORDER BY id DESC LIMIT 1";
                $db->Query($sql);

                $sql = "INSERT INTO tbl_umsatz (user, umsatzArt, artikel, price, speiseplan) VALUES('$userID', 2, $artikelID, $artikelPreis, $speiseplanID)";
                $db->Query($sql);
                //render();
                return 1;

            } else return 0 . "Ein Fehler ist aufgetreten! Bitte wenden Sie sich an den Administrator!";
        } else {
            return 0 . "Artikel können nicht unter 1 Tag vorher storniert werden";
        }
    }

    /**
     * @return string HREF URL zum zurückblättern abhängig von den kw/jahr GET Parametern
     */
    private function getLinkKWZuruck()
    {
        $currentKW = intval($_GET["kw"]);
        $currentJahr = intval($_GET["jahr"]);
        $currentKW--;

        if ($currentKW < 1) {
            $currentJahr--;
            $currentKW = $this->getWeeksInYear($currentJahr);
        }

        $currentKW = sprintf("%02d", $currentKW);
        $str = "?page=bestellen&kw=$currentKW&jahr=$currentJahr";
        return $str;
    }

    /**
     * @return string HREF URL zum vorblättern abhängig von den kw/jahr GET Parametern
     */
    private function getLinkKWVor()
    {
        $currentKW = intval($_GET["kw"]);
        $currentJahr = intval($_GET["jahr"]);
        $currentKW++;

        if ($currentKW > $this->getWeeksInYear($currentJahr)) {
            $currentJahr++;
            $currentKW = 1;
        }

        $currentKW = sprintf("%02d", $currentKW);
        $str = "?page=bestellen&kw=$currentKW&jahr=$currentJahr";
        return $str;
    }

    /**
     * Liefert die Anzahl der Kalenderwochen des angegegeben Jahres
     * Quelle: https://stackoverflow.com/questions/3319386/php-get-last-week-number-in-year
     * @param $year YYYY
     * @return int Anzahl der Kalenderwochen
     */
    private function getWeeksInYear($year)
    {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

    /**
     * Datumstext für den Titel der Tabelle
     * @return string Titel-Text
     */
    private function getCurrentDate()
    {
        $kw = $_GET["kw"];
        $jahr = $_GET["jahr"];
        $date = $this->getDateFromKWAndJahr($kw, $jahr);

        $montag = $date->format("d.m.Y");
        $sonntag = $date->add(new DateInterval("P6D"))->format("d.m.Y");
        return "KW $kw ($montag - $sonntag)";
    }
}
