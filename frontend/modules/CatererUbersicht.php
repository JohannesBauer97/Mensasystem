<?php
/**
 * Created by PhpStorm.
 * User: Alexander Ullmann
 */

require_once "CatererUbersichtBestellungen.php";
require_once "CatererUbersichtUmsatz.php";

class CatererUbersicht implements IModule
{
    private $tmp;

    /**
     * Dieses Modul dient nur zum Routing von 2 Unterseiten
     * @return mixed|string
     */
    public function render(){
        $this->tmp = new TemplateSystem();
        $this->tmp->load("content/caterer.ubersicht/Ubersicht.html");
        $this->manageContent();
        return $this->tmp->Template;
    }

    /**
     * Setzt anhand des overview Parameters die Unterseite
     */
    private function manageContent(){
        if(!isset($_GET["overview"])){
            header("location:index.php?page=home");
            exit;
        }
        switch($_GET["overview"]){
            case "bestellungen":
                $Bestellungen = new CatererUbersichtBestellungen();
                $this->tmp->setVar("UbersichtContent",$Bestellungen->render());
                break;
            case "umsatze":
                $Umsatze = new CatererUbersichtUmsatz();
                $this->tmp->setVar("UbersichtContent",$Umsatze->render());
                break;
            default:
                header("location:index.php?page=home");
                break;
        }
    }
}