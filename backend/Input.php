<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */
/**
 * This class is used for user inputs
 * Author Johannes Bauer
 */
class Input {
    /**
     * Searches for key in all superglobals
     * @param type $key any http-request key
     * @return returns value of key
     */
    public function get($key){
        if(array_key_exists($key, $_GET)){
            return $_GET[$key];
        }else if(array_key_exists($key, $_POST)){
            return $_POST[$key];
        }else if(array_key_exists($key, $_FILES)){
            return $_FILES($key);
        }
        
        return null;
    }
    
}
