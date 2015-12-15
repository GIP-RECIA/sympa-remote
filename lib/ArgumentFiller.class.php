<?php

/**
 * @Class ArgumentFiller
 *
 * @Purpose: Classe permettant de remplacer dans la chaine les parametres autorises par leur valeur.
 * (aussi appeles TOKENS)
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

class ArgumentFiller {

    /* logger */
    static private $log = null;

    /**** Configuration ****/
    static private $config = null;

     /* connexion au ldap */
    static private $ldap = null;

    /* Argument pret a etre passe au GroupSearcher */
    static private $filled_string = null;

    /**
     * Fonction permettant de creer l'argument a passer au GroupSearcher
     * Effectue les remplacement des variables par leur valeur dans les filtres de recherche
     * @param <type> $string la chaine a pre-traiter
     */
    public static function getFilledString($string,$array_values) {
        self::$log = $GLOBALS['logger'];
        self::$config = $GLOBALS['config'];
        $filled_string = $string;
        if (is_array($array_values)) {
            $res = preg_match_all("/{[^}]*}/", $string, $strings_to_replace);
            // Si on trouve des motifs a remplacer, on les remplace, sinon on retourne la meme chaine
            if ($res) {
                foreach ($strings_to_replace[0] as $string_to_replace) {
                    self::$log->LogDebug("ArgumentFiller : Remplacement de $string_to_replace");
                    $variable = substr($string_to_replace,1,-1);
                    if (array_key_exists($variable, $array_values)) {
                        $search = $string_to_replace;
                        $filled_string = str_replace($search, $array_values[$variable], $filled_string);
                        self::$log->LogDebug("ArgumentFiller : Nouvelle chaine : $filled_string");
                    }
                    else {
                        self::$log->LogError("ArgumentFiller : Impossible de remplacer $string_to_replace : variable non fournie ");
                        throw new ArgumentFillerException("VALUE USED BUT NOT PROVIDED $string_to_replace",1);
                    }
                }
            }

        }
        return $filled_string;
    }
    
    /**
     * En plus de getFilledString, cette fonction echape le paramètre pour LDAP.
     
     * @param <type> $string la chaine a pre-traiter
     */
    public static function getEscapedFilledString($string, $array_values) {
        self::$log = $GLOBALS['logger'];
        self::$config = $GLOBALS['config'];
        $filled_string = $string;
        if (is_array($array_values)) {
            $res = preg_match_all("/{[^}]*}/", $string, $strings_to_replace);
            // Si on trouve des motifs a remplacer, on les remplace, sinon on retourne la meme chaine
            if ($res) {
                foreach ($strings_to_replace[0] as $string_to_replace) {
                    self::$log->LogDebug("ArgumentFiller : Remplacement de $string_to_replace");
                    $variable = substr($string_to_replace,1,-1);
                    if (array_key_exists($variable, $array_values)) {
                        $search = $string_to_replace;
                        $filled_string = str_replace($search, self::ldap_escape($array_values[$variable]), $filled_string);
                        self::$log->LogDebug("ArgumentFiller : Nouvelle chaine : $filled_string");
                    }
                    else {
                        self::$log->LogError("ArgumentFiller : Impossible de remplacer $string_to_replace : variable non fournie ");
                        throw new ArgumentFillerException("VALUE USED BUT NOT PROVIDED $string_to_replace",1);
                    }
                }
            }

        }
        return $filled_string;
    }

    /**
     * Supprime les caracteres interdit de la chaine (caracteres email speciaux).
     * @param <type> $string la chaine a pre-traiter
     */
    public static function strip_string($string) {
		$stripped_string = $string;
		
		$stripped_string = self::strip_ldap_special_chars($stripped_string);
		$stripped_string = self::strip_email_special_chars($stripped_string);
		
        return $stripped_string;
    }

    /**
     * Echape les caracteres speciaux des requetes LDAP.
     * @param $s Requete à échapé
     * @param $d DN mode si true
     * @param $i String ou array de charactere à ne pas echaper
     * @return la requête échapée.
     */
    public static function ldap_escape ($s, $d = FALSE, $i = NULL) {
        $m = ($d) ? array(1 => '\\',',','=','+','<','>',';','"','#') : array(1 => '\\','*','(',')',chr(0));
        if (is_string($i) && ($l = strlen($s))) {
            for ($n = 0; $n < $l; $n++) if ($k = array_search(substr($s,$n,1),$m)) unset($m[$k]);
        } else if (is_array($i)) foreach ($i as $c) if ($k = array_search($c,$m)) unset($m[$k]);
            $q = array();
            foreach ($m as $k => $c) $q[$k] = '\\'.str_pad(dechex(ord($c)),2,'0',STR_PAD_LEFT);
        return str_replace($m,$q,$s);
    }

    /**
     * Supprime les caracteres speciaux de la partie locale d'une adresse mail.
     * @param $s partie locale d'une adresse mail
     * @return la chaine échapée.
     */
    public static function strip_email_special_chars ($s) {
    	self::$log = $GLOBALS['logger'];
        $result = preg_replace("/[^\\w!#\\$%&'\\*\\+\\-\\/=\\?\\^_`\\{\\|\\}~\\.]+/", "", $s);
        self::$log->LogDebug("striped_email_special_chars(1): $result");
        // Strip multiple dot
        $result = preg_replace("/[\\.]{2,}/", "", $result);
        self::$log->LogDebug("striped_email_special_chars(2): $result");

        return $result;
    }

    /**
     * Supprime les caracteres speciaux ldap.
     * @param $s la chaine à nettoyer 
     * @return la chaine échapée.
     */
    public static function strip_ldap_special_chars ($s) {
        self::$log = $GLOBALS['logger'];
        // Supprime le contenu des parantheses
        $result = preg_replace("/(\\x28.*\\x29)/", "", $s);
        self::$log->LogDebug("strip_ldap_special_chars(1): $result");
        // Supprime les caracteres speciaux LDAP
        $result = preg_replace("/(\\x28|\\x29|\\x2A|\\x5C|\\x00)+/", "", $result);
        self::$log->LogDebug("strip_ldap_special_chars(2): $result");
        $result = trim($result);
        // Remplace les espaces par des undersocre
        $result = preg_replace("/[\\s]+/", "_", $result);
        self::$log->LogDebug("strip_ldap_special_chars(3): $result");

        return $result;
    }


}

?>
