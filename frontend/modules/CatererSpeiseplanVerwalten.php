<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

class CatererSpeiseplanVerwalten implements IModule
{
    /**
     * @var TemplateSystem Speiseplanverwaltung
     */
    private $tmp;
    /**
     * @var Connection Datenbank
     */
    private $db;

    /**
     * Gibt den HTML Code für das Modul Speiseplan Verwaltung zurück
     * @return mixed|string
     */
    public function render()
    {
        $this->tmp = new TemplateSystem();
        $this->db = new Connection();
        JSController::getInstance()->loadJSFile("caterer.speiseplan.verwaltung.js");

        if (!isset($_GET["kw"])) {
            $_GET["kw"] = date("W");
        }

        if (!isset($_GET["jahr"])) {
            $_GET["jahr"] = date("Y");
        }

        $this->tmp->load("content/caterer.speiseplan/Verwalten.html");
        $this->tmp->setVar("DauerhaftImAngebot", $this->createDauerhaftImAngebot());
        $this->tmp->setVar("ArtikelOptions", $this->getArtikelSelectOptions());
        $this->tmp->setVar("CurrentDate", $this->getCurrentDate());
        $this->tmp->setVar("LinkKWZuruck", $this->getLinkKWZuruck());
        $this->tmp->setVar("LinkKWVor", $this->getLinkKWVor());
        $this->tmp->setVar("SpeiseplanTableBody", $this->getSpeiseplanTableBody($_GET["kw"], $_GET["jahr"]));
        $this->setSelectBoxDate();

        return $this->tmp->Template;
    }

    /**
     * Erstellt den Speiseplan Inhalt HTML Code
     * @param $kw Kalenderwoche
     * @param $jahr Jahr
     * @return string HTML Code
     * @throws Exception
     */
    public static function getSpeiseplanTableBody($kw, $jahr)
    {
        $date = self::getDateFromKWAndJahr($kw, $jahr);
        $montag = $date->format("Y-m-d 00:00:00");
        $dienstag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $mittwoch = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $donnerstag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $freitag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $samstag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $sonntag = $date->add(new DateInterval("P1D"))->format("Y-m-d 00:00:00");
        $wochentage = array($montag, $dienstag, $mittwoch, $donnerstag, $freitag, $samstag, $sonntag);

        $db = new Connection();
        $sql = "SELECT
                    tbl_speiseplan.timestamp,
                    tbl_speiseplan.id,
                    tbl_artikel.id as 'ArtikelID',
                    tbl_artikel.title
                FROM
                    `tbl_speiseplan`
                LEFT JOIN 
                    `tbl_artikel`
                ON tbl_artikel.id = tbl_speiseplan.artikel
                WHERE
                    `timestamp` BETWEEN '$montag' AND '$sonntag'
                AND
                    tbl_speiseplan.`active`=1";
        $result = $db->Query($sql);
        if ($result == null || count($result) <= 0) {
            return "<td colspan='9' style='text-align: center;'>Noch kein Essen angelegt!</td>";
        }

        $output = "";
        $output .= "<td></td>";
        foreach ($wochentage as $tag) {
            $output .= "<td><div class='list-group'>";
            foreach ($result as $eintrag) {
                if ($eintrag["timestamp"] == $tag) {
                    $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}' speiseplanid='{$eintrag["id"]}'>{$eintrag["title"]}</button>";
                }
            }
            $output .= "</div></td>";
        }

        $output .= "<td></td>";
        return $output;
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
     * DropDown Options aller aktiven Artikel
     * @return string HTML Code
     */
    private function getArtikelSelectOptions()
    {
        $output = "";
        $result = $this->db->Query("SELECT * FROM `tbl_artikel` WHERE `active`=1 AND `dauerhaftesAngebot`=0");

        if ($result == null) {
            return "";
        }

        foreach ($result as $item) {
            $preis = number_format($item["price"], 2, ",", ".");
            $output .= "<option value='{$item["id"]}'>{$item["title"]} ({$preis}€)</option>";
        }

        return $output;
    }

    /**
     * Fügt einen Artikel zum Speiseplan hinzu
     * @param $artikelID ArtikelID
     * @param $date Datum
     * @return array|bool|int|null
     */
    public static function addArtikelTSpeiseplan($artikelID, $date)
    {
        $artikelID = intval($artikelID);
        $date = strtotime($date);
        if($artikelID <= 0){
            return 0;
        }
        $date = date("Y-m-d 00:00:00", $date);
        $sql = "INSERT INTO `tbl_speiseplan`(`timestamp`, `artikel`) VALUES ('$date', $artikelID)";
        $db = new Connection();
        $result = $db->Query($sql);

        return $result;
    }

    /**
     * Datumstext für den Titel der Verwaltungstabelle
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
        $str = "?page=speiseplan&subpage=verwalten&kw=$currentKW&jahr=$currentJahr";
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
        $str = "?page=speiseplan&subpage=verwalten&kw=$currentKW&jahr=$currentJahr";
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
     * Setzt im Template die Dates für alle SelectBoxen
     */
    private function setSelectBoxDate()
    {

        $date = $this->getDateFromKWAndJahr($_GET["kw"], $_GET["jahr"]);
        $montag = $date->format("d.m.Y");
        $dienstag = $date->add(new DateInterval("P1D"))->format("d.m.Y");
        $mittwoch = $date->add(new DateInterval("P1D"))->format("d.m.Y");
        $donnerstag = $date->add(new DateInterval("P1D"))->format("d.m.Y");
        $freitag = $date->add(new DateInterval("P1D"))->format("d.m.Y");
        $samstag = $date->add(new DateInterval("P1D"))->format("d.m.Y");
        $sonntag = $date->add(new DateInterval("P1D"))->format("d.m.Y");

        $this->tmp->setVar("date-Montag", $montag);
        $this->tmp->setVar("date-Dienstag", $dienstag);
        $this->tmp->setVar("date-Mittwoch", $mittwoch);
        $this->tmp->setVar("date-Donnerstag", $donnerstag);
        $this->tmp->setVar("date-Freitag", $freitag);
        $this->tmp->setVar("date-Samstag", $samstag);
        $this->tmp->setVar("date-Sonntag", $sonntag);
    }

    /**
     * Gibt den HTML Code für die Details eines Artikels
     * @param $artikelID ArtikelID
     * @param $speiseplanID SpeiseplanID
     * @return string|void HTML Code
     */
    public static function getArtikelModal($artikelID, $speiseplanID){
        $artikelID = intval($artikelID);
        $speiseplanID = intval($speiseplanID);
        if($artikelID <= 0 || $speiseplanID <= 0){
            return "Fehler: Falsche ID";
        }

        $db = new Connection();
        $result = $db->Query("SELECT * FROM `tbl_artikel` WHERE `id`=$artikelID LIMIT 1");
        if(!$result){
            return "Fehler: Falsche ID";
        }
        $artikel = $result[0];
        $preis = number_format($artikel["price"],2,",",".");
        $tmpl = new TemplateSystem();
        $tmpl->load("content/caterer.speiseplan/VerwaltenAnzeigenModal.html");
        $tmpl->setVar("Title", $artikel["title"]);
        $tmpl->setVar("Preis", $preis);
        $tmpl->setVar("Beschreibung", $artikel["description"]);
        $tmpl->setVar("SpeiseplanID", $speiseplanID);

        return $tmpl->show();
    }

    /**
     * Löscht einen Eintrag im Speiseplan und storniert ggbfs. vorhandene Buchnungen
     * @param $speiseplanID
     */
    public static function deleteFromSpeiseplan($speiseplanID){
        $speiseplanID = intval($speiseplanID);
        $db = new Connection();
        $sqlGetBuchungen = "SELECT 
                                *
                            FROM
                                tbl_umsatz
                            WHERE
                                tbl_umsatz.umsatzArt = 1
                                AND
                                tbl_umsatz.active = 1
                                AND
                                tbl_umsatz.speiseplan = $speiseplanID";
        $buchungen = $db->Query($sqlGetBuchungen);
        if ($buchungen != null) {
            foreach ($buchungen as $buchung) {
                $id = $buchung["id"];
                $user = $buchung["user"];
                $artikel = $buchung["artikel"];
                $price = $buchung["price"];

                //Buchung stornieren
                $db->Query("UPDATE tbl_umsatz SET active=0 WHERE id=$id");
                $db->Query("INSERT INTO `tbl_umsatz` (`timestamp`, `user`, `umsatzArt`, `artikel`, `price`, `speiseplan`) VALUES (CURRENT_TIMESTAMP, $user, 2, $artikel, $price, $speiseplanID)");
                $db->Query("UPDATE tbl_user SET kontostand=kontostand+$price WHERE id=$user");
            }
        }

        $result = $db->Query("UPDATE tbl_speiseplan 
                                    SET 
                                        active = 0
                                    WHERE
                                        id = $speiseplanID");
        if ($result)
            return 1;
        else
            return 0;
    }

    /**
     * Gibt die Anzahl an Buchungen für einen Speiseplanartikel zurück
     * @param $speiseplanID
     * @return int
     */
    public static function getBuchungen($speiseplanID)
    {
        $speiseplanID = intval($speiseplanID);
        $db = new Connection();
        $result = $db->Query("SELECT 
                                    COUNT(*) as 'buchungen'
                                FROM
                                    tbl_umsatz
                                WHERE
                                    speiseplan = $speiseplanID
                                    AND
                                    active=1
                                    AND
                                    umsatzArt=1");
        $buchungen = intval($result[0]["buchungen"]);
        return $buchungen;
    }
}