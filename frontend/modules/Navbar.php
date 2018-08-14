<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

class Navbar
{
    private $Navtmp;
    private $auth;
    private $page;

    /**
     * Gibt den HTML Code für Navbar zurück
     * @return string HTML Code
     */
    public function render()
    {
        $this->Navtmp = new TemplateSystem();
        $this->auth = Auth::getInstance();
        $this->Navtmp->load("navbar/Nav.html");
        $this->Navtmp->setVar("DateAttr", "&kw=" . date("W") . "&jahr=" . date("Y"));


        if(!isset($_GET["page"])){
            $_GET["page"] = "home";
        }
        $this->page = $_GET["page"];

        if ($this->auth->getUser()){
            $user = $this->auth->getUser();
            if($user["rolle"] == 1){
                $this->Navtmp->setVar("Links",$this->createCatererMenu());
            }else{
                $this->Navtmp->setVar("Links",$this->createKundenMenu());
            }
        }else{
            $this->Navtmp->setVar("Links",$this->createPublicMenu());
        }

        return $this->Navtmp->Template;
    }

    /**
     * Erstellt den HTML Code für die Navbar des Caterers
     * @return string HTML Code
     */
    private function createCatererMenu(){
        $catererNav = new TemplateSystem();
        $catererNav->load("navbar/caterer.html");
        $catererNav->setVar("DateAttr", "&kw=" . date("W") . "&jahr=" . date("Y"));

        switch ($this->page){
            case "home":
                $catererNav->setVar("navHomeClass","active");
                $catererNav->setVar("navHomeMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "kunden":
                $catererNav->setVar("navKundenClass","active");
                $catererNav->setVar("navKundenMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "motd":
                $catererNav->setVar("navMOTDClass","active");
                $catererNav->setVar("navMOTDMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "ubersicht":
                $catererNav->setVar("navUbersichtClass","active");
                $catererNav->setVar("navUbersichtMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "speiseplan":
                $catererNav->setVar("navSpeiseplanClass","active");
                $catererNav->setVar("navSpeiseplanMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            default:
                $catererNav->setVar("navHomeClass","active");
                $catererNav->setVar("navHomeMobileMarker","<span class=\"sr-only\">(current)</span>");
        }
        return $catererNav->Template;
    }

    /**
     * Erstellt den HTML Code für die Navbar der Kunden
     * @return string HTML Code
     */
    private function createKundenMenu(){
        $kundenNav = new TemplateSystem();
        $kundenNav->load("navbar/kunden.html");
        $kundenNav->setVar("DateAttr", "&kw=" . date("W") . "&jahr=" . date("Y"));

        switch ($this->page){
            case "home":
                $kundenNav->setVar("navHomeClass","active");
                $kundenNav->setVar("navHomeMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "bestellen":
                $kundenNav->setVar("navBestellenClass","active");
                $kundenNav->setVar("navBestellenMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "profil":
                $kundenNav->setVar("navProfilClass","active");
                $kundenNav->setVar("navProfilMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "logout":
                $kundenNav->setVar("navLogoutClass","active");
                $kundenNav->setVar("navLogoutMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;

        }
        return $kundenNav->Template;
    }

    /**
     * Erstellt den HTML Code für die Navbar der ausgeloggten User
     * @return string HTML Code
     */
    private function createPublicMenu(){
        $publicNav = new TemplateSystem();
        $publicNav->load("navbar/public.html");
        $publicNav->setVar("DateAttr", "&kw=" . date("W") . "&jahr=" . date("Y"));

        switch ($this->page){
            case "home":
                $publicNav->setVar("navHomeClass","active");
                $publicNav->setVar("navHomeMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            case "login":
                $publicNav->setVar("navLoginClass","active");
                $publicNav->setVar("navLoginMobileMarker","<span class=\"sr-only\">(current)</span>");
                break;
            default:
                $publicNav->setVar("navHomeClass","active");
                $publicNav->setVar("navHomeMobileMarker","<span class=\"sr-only\">(current)</span>");
        }
        return $publicNav->Template;
    }

}