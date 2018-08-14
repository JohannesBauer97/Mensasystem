<?php
/**
 * Created by PhpStorm.
 * User: Natalia Ortega
 */

class CatererMOTD implements IModule
{
    /**
     * @var TemplateSystem
     */
    private $tmp;
    /**
     * @var Connection
     */
    private $db;

    /**
     * Erstellt den HTML Code für die MOTD Seite
     * @return mixed|string HTML Code
     */
    public function render(){
        JSController::getInstance()->loadJSFile("caterer.motd.js");
        $this->tmp = new TemplateSystem();
        $this->db = new Connection();
        $this->tmp->load("content/caterer.motd/MOTD.html");
        $this->tmp->setVar("MOTDList", $this->getMotdList());

        return $this->tmp->Template;
    }

    /**
     * Erstellt den HTML Code für die vorhandenes MOTDs
     * @return string HTML Code
     */
    private function getMotdList()
    {
        $result = $this->db->Query("SELECT * FROM `tbl_motd`");

        if ($result == null) {
            return "<i>Noch keine MOTDs angelegt!</i>";
        }

        $output = "";
        foreach ($result as $row) {
            if ($row["active"] == 1) {
                $output .= "<button onclick='toggleMotd({$row["id"]})' type=\"button\" class=\"list-group-item list-group-item-action active\">{$row["msg"]}</button>";
            } else {
                $output .= "<button onclick='toggleMotd({$row["id"]})' type=\"button\" class=\"list-group-item list-group-item-action \">{$row["msg"]}</button>";
            }
        }

        return $output;
    }

    /**
     * Aktiviert/Deaktiviert ein MOTD
     * Deaktiviert automatisch alle anderen MOTDs
     * @param $motdID MOTD Tabellen PrimaryKey
     * @return array|bool|null|string
     */
    public static function toggleMOTD($motdID)
    {
        $motdID = intval($motdID);
        $db = new Connection();
        $result = $db->Query("SELECT * FROM `tbl_motd` WHERE `id` = $motdID");

        if ($result == null) {
            return "Error: Falsche ID!";
        }

        $active = $result[0]["active"];

        if ($active == 1) {
            $active = 0;
        } else {
            $active = 1;
        }


        $db->Query("UPDATE `tbl_motd` SET `active`=0 WHERE 1");
        return $db->Query("UPDATE `tbl_motd` SET `active`=$active WHERE `id`=$motdID");
    }

    /**
     * Legt eine neue MOTD an
     * @param $msg Nachricht
     * @return int True oder False
     */
    public static function motdAnlegen($msg)
    {
        if (empty($msg)) {
            return 0;
        }
        $db = new Connection();
        $msg = $db->filter($msg, true);
        $db->Query("INSERT INTO `tbl_motd`(`msg`) VALUES ('$msg')");

        return 1;
    }
}

