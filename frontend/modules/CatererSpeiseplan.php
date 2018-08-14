<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

require_once "CatererSpeiseplanArtikel.php";
require_once "CatererSpeiseplanVerwalten.php";

class CatererSpeiseplan implements IModule
{
    private $tmp;

    /**
     * Das Modul dient nur dem Routing zwischen 2 Unterseiten
     * @return mixed|string
     */
    public function render(){
        $this->tmp = new TemplateSystem();
        $this->tmp->load("content/caterer.speiseplan/Speiseplan.html");
        $this->manageContent();
        return $this->tmp->Template;
    }

    /**
     * Setzt anhand des subpage Parameters die Unterseite
     */
    private function manageContent(){
        if(!isset($_GET["subpage"])){
            header("location:index.php?page=home");
            exit;
        }
        switch($_GET["subpage"]){
            case "verwalten":
                $Verwalten = new CatererSpeiseplanVerwalten();
                $this->tmp->setVar("SpeiseplanContent",$Verwalten->render());
                break;
            case "artikel":
                $Artikel = new CatererSpeiseplanArtikel();
                $this->tmp->setVar("SpeiseplanContent",$Artikel->render());
                break;
            default:
                header("location:index.php?page=home");
                break;
        }
    }
}