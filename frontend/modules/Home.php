<?php
/**
 * Created by PhpStorm.
 * User: Alexander Ullmann
 */
class Home implements IModule
{
    private $tmp;
    /**
     * @var Connection
     */
    private $db;

    /**
     * Gibt den HTML Code der öffentlichen Startseite zurück
     * @return mixed|string
     */
    public function render()
    {
        JSController::getInstance()->loadJSFile("home.js");
        $this->db = new Connection();

        if (!isset($_GET["kw"])) {
            $_GET["kw"] = date("W");
        }

        if (!isset($_GET["jahr"])) {
            $_GET["jahr"] = date("Y");
        }
        $this->tmp = new TemplateSystem();
        $this->tmp->load("content/home/Home.html");
        $this->tmp->setVar("SpeiseplanTableBody", $this->getSpeiseplanTableBody($_GET["kw"], $_GET["jahr"]));
        $this->tmp->setVar("LinkKWZuruck", $this->getLinkKWZuruck());
        $this->tmp->setVar("LinkKWVor", $this->getLinkKWVor());
        $this->tmp->setVar("CurrentDate", $this->getCurrentDate());
        $this->tmp->setVar("MOTD", $this->getMOTD());
        $this->tmp->setVar("DauerhaftImAngebot", $this->getDauerhaftImAngebot());
        $this->tmp->setVar("Ranking", $this->getRanking());

        return $this->tmp->Template;
    }

    /**
     * Gibt den HTML Code für die MOTD zurück
     * @return string|void HTML Code
     */
    private function getMOTD()
    {
        $result = $this->db->Query("SELECT * FROM `tbl_motd` WHERE `active` = 1");
        if ($result == null) {
            return;
        }
        return "<div class=\"row\">
                    <div class=\"col-12\">
                        <div class=\"alert alert-primary\" role=\"alert\">
                            <div class=\"row\">
                                <i class=\"material-icons\" style=\"margin-right:5px;\">info</i>
                                {$result[0]["msg"]}
                            </div>
                        </div>
                    </div>
                </div>";
    }

    /**
     * Liefert den HTML Code für den Body der Speiseplan Tabelle auf der Home Seite
     * @param $kw Kalenderwoche
     * @param $jahr Jahr
     * @return string HTML Code
     * @throws
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
        if (count($result) <= 0) {
            return "<tr><td colspan='9' style='text-align: center;'>Noch kein Essen angelegt!</td></tr>";
        }

        $output = "";
        $output .= "<td></td>";
        foreach ($wochentage as $tag) {
            $output .= "<td><div class='list-group'>";
            foreach ($result as $eintrag) {
                if ($eintrag["timestamp"] == $tag) {
                    $preis = number_format($eintrag["price"], 2, ",", ".") . "€";
                    $output .= "<button type=\"button\" class=\"list-group-item list-group-item-action speiseplan-artikel-btn\" artikelid='{$eintrag["ArtikelID"]}'>{$eintrag["title"]} ($preis)</button>";
                }
            }
            $output .= "</div></td>";
        }

        $output .= "<td></td>";
        return $output;
    }

    /**
     * Gibt die ListItems der dauerhaften Angebote zurück
     * @return string HTML Code
     */
    private function getDauerhaftImAngebot()
    {

        $db = new Connection();
        $output = "";
        $sql = "SELECT title, id FROM tbl_artikel WHERE dauerhaftesAngebot = 1 AND active=1";
        $dauerhaftimangebot = $db->Query($sql);

        if ($dauerhaftimangebot == null)
            return "<li>Kein dauerhaftes Angebot!</li>";

        foreach ($dauerhaftimangebot as $dia) {
            $output .= "<li><a href='#' onclick='openArtikelModal({$dia["id"]})'>{$dia["title"]}</a></li>";
        }


        return $output;
    }

    /**
     * Gibt die ListItems der Top gelikten Artikel zurück
     * @return string HTML Code
     */
    private function getRanking()
    {
        $result = $this->db->Query("SELECT 
                                            tbl_artikel.title,
                                            tbl_artikel.id,
                                            COUNT(tbl_artikel_likes.id) as 'votes'
                                        FROM
                                            tbl_artikel_likes
                                        LEFT JOIN tbl_artikel ON tbl_artikel.id = tbl_artikel_likes.artikel
                                        WHERE tbl_artikel.active = 1
                                        GROUP BY artikel
                                        ORDER BY votes DESC
                                        LIMIT 10");
        if ($result == null)
            return "Es wurde noch keine Artikel geliked!";

        $output = "";
        foreach ($result as $entry) {
            $output .= "<li><a href='#' onclick='openArtikelModal({$entry["id"]})'>{$entry["title"]}</a></li>";
        }

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
        $str = "?page=home&kw=$currentKW&jahr=$currentJahr";
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
        $str = "?page=home&kw=$currentKW&jahr=$currentJahr";
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

    /**
     * Liefert den HTML Code mit den Details zu einem Artikel
     * @param $artikelID Artikel ID
     * @return string|void HTML Code
     */
    public static function getArtikelModal($artikelID)
    {
        $artikelID = intval($artikelID);
        if ($artikelID <= 0) {
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
        $tmpl->load("content/home/ArtikelModal.html");
        $tmpl->setVar("Title", $artikel["title"]);
        $tmpl->setVar("Preis", $preis);
        $tmpl->setVar("Beschreibung", $artikel["description"]);

        return $tmpl->show();
    }
}
