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

}

?>
