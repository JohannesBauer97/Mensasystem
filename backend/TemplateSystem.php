<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */
/**
 * PHP Template System
 * Author Johannes Bauer
 */
class TemplateSystem {
    private $VarDelimiter = array("{..","..}");
    private $TemplateIncludeRegex = '/\{Include="(.+[.]html)"\}/';
    private $RemoveVarRegex = '/{\.\.(.+)\.\.}/';
    private $TemplateFolderPath;
    public $Template = "";
    
    
    public function __construct() {
        $this->setTemplateFolderPath();
    }
    
    /**
     * Sets the Template Folder Path with Directory Seperator at the end
     */
    private function setTemplateFolderPath(){
        $this->TemplateFolderPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "frontend" . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Loads templatefile
     * @param type $template Name of templatefile
     */
    public function load($template){
        if(!file_exists($this->TemplateFolderPath . $template)){
            echo("Could not found: " . $this->TemplateFolderPath . $template);
            return false;
        }
        
        $this->Template = file_get_contents($this->TemplateFolderPath . $template);
        $this->parseFunctions();
        return true;
    }
    
    /**
     * Parses includes
     */
    private function parseFunctions(){
        while(preg_match($this->TemplateIncludeRegex, $this->Template)){
            $this->Template = preg_replace_callback($this->TemplateIncludeRegex, function($treffer){
                if(!$this->load($treffer[1])){
                    return "Template not found.";
                }
                return $this->Template;
            }, $this->Template);
        }
    }
    
    /**
     * Sets variable in template.
     * @param type $key Variable name
     * @param type $val Value
     */
    public function setVar($key, $val) {
        $this->Template = str_replace( $this->VarDelimiter[0] . $key . $this->VarDelimiter[1], $val, $this->Template );
    }
    
    /**
     * Displays/echos template.
     */
    public function show(){
        $this->removeUnusedVars();
        echo $this->Template;
    }
    
    /**
     * Removes all unset variables in Template
     */
    private function removeUnusedVars(){
        $this->Template = preg_replace($this->RemoveVarRegex,"",$this->Template);
    }
}
