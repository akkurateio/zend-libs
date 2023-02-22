<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur le serveur  
 */
class LibServer
{
    /**
     * Vérifie si le serveur est en https
     * @return bool
     */
    public static function is_secure() {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }
        
        return $isSecure;
    }
    
    public static function get_protocole() {
        $isSecure = self::is_secure();
        if($isSecure == true) {
            return 'https://';
        }
        else {
            return 'http://';
        }
    }
}
