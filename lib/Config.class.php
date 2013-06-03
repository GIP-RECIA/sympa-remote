<?php

/**
 * @Class Config
 *
 * @Purpose: Classe permettant de charger et stocker la configuration
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 * Cette classe permet de charger la configuration et de la rendre
 * disponible
 *
 */

class Config {

    /*
     * Parametres a lire dans le fichier de configuration.
     */
    private $debug_level = null;

    private $log_file = null;

    private $sympa_bin_dir = null;

    private $param_method = null;

    private $db_host = null;
    private $db_user = null;
    private $db_pass = null;
    private $db_db = null;

    // Scenarios d'ecritures sympa autorises par sympa-remote
    private $authorized_send_scenario = null;

    // Domaine principal sympa
    private $sympa_main_domain = null;
   
    private $owners_group_filter = null;

    # Le chemin vers le fichier de configuration associe a la classe Config.
    const config_file = "config/config.inc.php";

    /**
     * Constructeur
     */
    public function __construct() {
        # On charge la configuration et on etablit la connexion si
        # le serveur a ete configure.
        if (self::is_setup()) {
            $this->load_config();
        }
    }

    /**
     * Permet de recupere une valeur de configuration possiblement contenu dans un array.
     * Retourne la valeur a la position $index dans l'array ou bien $object si $object n'est pas un array.
     * @return <type> String
     */
    public static function get_array_value($confValue, $index = "Default") {
	$value = $confValue;
	if (is_array($confValue)) {
	    if (empty($confValue)) {
			throw new Exception("Config Array is empty !", 1);
            exit(1);
	    }

	    // If config value is an array
	    if (isset($confValue[$index])) {
			// If the index is present in the array
	    	$value = $confValue[$index];
	    } else {
			// Index does not exist in array => default value is the first
			$value = reset($confValue);
	    }
	}

        return $value;
    }

    /**
     * Permet de verifier que le fichier de configuration correspondant
     * existe bien
     * @return <type> booleen
     */
    public static function is_setup() {
        return file_exists(dirname(__FILE__)."/../".self::config_file);
    }

    # On recupere les donnees de configuration presentes dans le fichier
    # /config/config.inc.php
    private function load_config() {
        $ldap_config = array();
        if (self::is_setup()) {
            $path = dirname(__FILE__)."/../".self::config_file;
            include($path);

            $available_settings = get_class_vars(get_class($this));
            foreach($available_settings as $key => $value) {
                $varname = $key;
                if (isset($$varname)) {

                    $this->$key = $$varname;
                    if (is_array($$varname)) {
                        if (count($$varname)) {
                            foreach($$varname as $key2 => $value2) {
                            }
                        }
                    }
                }
            }

        }
    }

    public function  __get($name) {
        return $this->$name;
    }

    /**
     * Retourne les variables de la classe
     * @return <type> Les variables de la classe
     */
    public function get_config_vars() {
        return get_class_vars(__CLASS__);
    }

}

?>
