<?php

/**
 * @Class GroupSearcher
 *
 * @Purpose: Classe permettant de rechercher un groupe dans la branche groups
 * du LDAP, a partir d'une expression. (Recherche sur le CN, c'est à dire
 * le nom du groupe)
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

class GroupSearcher {

    /* logger */
    static private $log = null;

    /**** Configuration ****/
    static private $config = null;

    /**** Connexion LDAP ****/
    static private $ldap = null;
    
    public static function search($search_argument) {
        self::$log = $GLOBALS['logger'];
        self::$config = $GLOBALS['config'];
        self::initialize_ldap_connection();
        return self::do_ldap_search($search_argument);
    }

    private static function do_ldap_search($arg) {
        $group = self::$ldap->search_group(utf8_encode($arg));
        if ($group) {
            self::$log->LogDebug("GroupSearcher : Group found = $group");
        }
        return utf8_decode($group);
    }

    private static function initialize_ldap_connection() {
        try {
            self::$log->LogDebug("GroupSearcher : Initializing ldap connection");
            self::$ldap = new LDAPServer();
        }
        catch(Exception $e) {
            $message = "Error initializing LDAP connection : ".$e->getMessage();
            self::$log->LogFatal($message);
            throw new Exception("LDAP CONNECTION ERROR");
        }
    }

}

?>
