<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */
require_once 'Config.php';

class Connection {
    
    private $link;
    private $connectError;
    private $lastError;
    
    /**
     * Setup new MySQLi connection
     */
    public function __construct() {
        $this->link = new \mysqli(DBSERVER, DBUSER, DBPASSWORD, DBNAME) or die;
        $this->link->set_charset("utf8");
        $this->connectError = $this->link->connect_error;
    }
    
    /**
     * Führt ein SQL Statement auf der Datenbank aus
     * @param type $sql SQL Command
     * @return Array|bool|null DataArray, Response Bool
     */
    public function Query($sql){
        $data = mysqli_query($this->link, $sql);
        $this->lastError = $this->link->error;

        if(is_bool($data)) return $data;
        
        $out = array();
        $i = 0;
        while($row= mysqli_fetch_object($data)){        
            foreach($row as $key => $value){
                $out[$i][$key] = $value;
            }
            $i++;
        }
        if($i == 0)
            return null;
        return $out;
    }

    /**
     * @return mysqli Gibt die MySQL Schnittstelle zurück
     */
    public function getLink(){
        return $this->link;
    }

    /**
     * @return string Gibt den letzten Verbindungsfehler zurück
     */
    public function getConnectError(){
        return $this->connectError;
    }

    /**
     * @return mixed Gibt den letzten Query Fehler zurück
     */
    public function getLastError(){
        return $this->lastError;
    }

    /**
     * @return int|string Gibt die letzte hinzugefügte Row ID zurück
     */
    public function LastInsertedID(){
        return mysqli_insert_id($this->link);
    }

    /**
     * Filtert Usereingaben gegen SQL Injections und Cross-Site-Scripting
     * @param $input Usereingabe
     * @param bool $htmlentities Besondere HTML Charakter escapen
     * @return string Gefilterter Wert
     */
    public function filter($input,$htmlentities = true){
        if($htmlentities){
            $input = htmlentities($input);
        }
        
        $input = mysqli_real_escape_string($this->link,$input);
        return $input;
    }
}
