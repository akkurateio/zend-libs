<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions utilitaires sur les string   
 */
class LibString {

    /**
     * Permet de "caméliser" une chaine de caractéres
     * Ex : "string de test" => "stringDeTest" (StringDeTest si pascalCase)
     * 
     * @param string $string
     * @param boolean $pascalCase
     * @return string
     */
    public static function camelize($string, $pascalCase = false) 
    { 
        $string = self::stripAccents($string);
        $string = str_replace(array('-', '_', '(', ')'), ' ', strtolower($string)); 
        $string = trim($string);
        $string = ucwords($string); 
        $string = str_replace(' ', '', $string);  

        if (!$pascalCase) { 
            return lcfirst($string); 
        } 
        return $string; 
    }
    
    /**
     * Filter a name to only allow valid variable characters
     *
     * @param  string $value
     * @param  bool $allowBrackets
     * @return string
     */
    public static function zendCamelize($value, $allowBrackets = false)
    {
	$charset = '^a-zA-Z0-9_\x7f-\xff';
        if ($allowBrackets) {
            $charset .= '\[\]';
        }
        return preg_replace('/[' . $charset . ']/', '', (string) $value);
    }
    
    /**
     * Permet de "décaméliser" une chaine de caractéres
     * Ex : "stringDeTest" => "string_de_test"
     * 
     * @param string $string
     * @return string 
     */
    public static function uncamelize($string){
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);

    }
    
    /**
     * Permet de mettre une chaine de caractéres en format "constante"
     * Ex : "string de test (encore)" => "STRING_DE_TEST_ENCORE"
     * 
     * @param string $string
     * @return string
     */
    public static function constantize($string){
        $string = self::stripAccents($string);
        $string = str_replace(array('"', '\'',), '', $string);
        $string = str_replace(array(' (', '( ', '(', ')',), ' ', $string);
        $string = trim($string);
        $string = str_replace(array(' ', '-', '(', ')', ',', ';', ':'), '_', $string); 
        
        $string = strtoupper($string);  

        return $string; 
    }
 
    /**
     * Permet de supprimer les accents d'une chaine de caracteres
     * 
     * @param string $string
     * @return string
     */
    public static function stripAccents($string){
        $str = htmlentities($string, ENT_NOQUOTES, 'utf-8');
 
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractéres
 
        return $str;
    }
    
    /**
     * Permet "iemiser" le contenu d'une chaine de caractéres pour de l'affichage HTML
     * Ex iemizeStr('le 84éme élément', 'iéme') => "le 84<sup>iéme</sup> élément"
     * 
     * @param string $str
     * @param string $strToIemized
     * @return string
     */
    public static function iemizeStr($str, $strToIemized){
        return trim(str_replace($strToIemized, "<sup>$strToIemized</sup>", $str));
    }
    
    /**
     * Fonction de callback
     * Permet d'encadrer une chaine par des quotes
     * 
     * @param string $str
     * @return string
     */
    public static function escape($str){
        return "'$str'";
    }
    
    /**
     * Fonction de callback
     * Permet de mettre en majuscule et d'encadrer une chaine par des quotes
     * 
     * @param string $str
     * @return string
     */
    public static function upperAndEscape($str){
        $str = strtoupper($str);
        return self::escape($str);
    }
    
    /**
     * Fonction d'utilitaire pour CLI
     * Permet de forcer l'affichage temps réel d'une chaine de caractéres
     * 
     * @param string $string 
     */
    public static function flush($string){
        echo $string;
        ob_flush();
    }
	
    /**
     * Fonction permettant de formater un numéro de téléphone selon le caractére
     * $sep fourni en paramètre
     * 
     * @param string $tel
     * @param string $sep
     * @return string 
     */
    public static function getFormatedTel($tel, $sep = '.') {
        $result = $tel;
        if (preg_match('/(.*)(\d{2})(\d{2})(\d{2})(\d{2})$/', $tel, $matches)) {
            $result = $matches[1] . $sep . $matches[2] . $sep . $matches[3] . $sep . $matches[4] . $sep . $matches[5];
        }
        return $result;
    }
    
    /**
     * Supprime tous les caractères non alpha numérique d'une string
     * @param string $string
     * @return string
     */
    public static function keepAlphaNumeric($string) {
        $stringFormat = preg_replace("/[^a-zA-Z0-9]+/", "", $string);
        
        return $stringFormat;
    }
    
    /**
     * Récupération des tags dans une chaine de caractère
     * @param string $string
     * @return array
     */
    public static function getTagsFromString($string, $prefix = '#') {
        $tagsList = array();
        preg_match_all('/((^'.$prefix.'[[:graph:]]*)|([ \r\n]{1}('.$prefix.'[[:graph:]]*)))/i', $string, $tagsList);

        if (isset($tagsList[0]) && !empty($tagsList[0])) {
            return array_filter(array_map('trim', $tagsList[0]));
        } else {
            return array();
        }
    }
    
    /**
     * Récupération des contacts dans une chaine de caractère
     * @param string $string
     * @return array
     */
    public static function getContactsFromString($string, $prefix = '@') {
        $contactsList = array();
        preg_match_all('/((^'.$prefix.'[[:graph:]]*)|([ \r\n]{1}('.$prefix.'[[:graph:]]*)))/i', $string, $contactsList);

        if (isset($contactsList[0]) && !empty($contactsList[0])) {
            return array_filter(array_map('trim', $contactsList[0]));
        } else {
            return array();
        }
    }

    public static function strposX($haystack, $needle, $number){
        if($number == '1'){
            return strpos($haystack, $needle);
        }elseif($number > '1'){
            return strpos($haystack, $needle, self::strposX($haystack, $needle, $number - 1) + strlen($needle));
        }else{
            return false;
        }
    }
}