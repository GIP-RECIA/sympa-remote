<?php

/**
 * @Class ConfigChecker
 *
 * @Purpose: Classe permettant de verifier que la configuration interne
 * (fichier de parametrage) est correcte
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

class ConfigChecker {

    /* logger */
    static private $log = null;

    /**** Configuration ****/
    static private $config;

    
    /**
     * Methode principale de la classe, permet de verifier que la configuration (config.inc.php)
     * de sympa-remote est correcte.
     */
    public static function check() {
        self::$config = $GLOBALS['config'];
        self::$log = $GLOBALS['logger'];
        self::check_config();
    }

    /**
     * Methode realisant la vérification
     */
    private static function check_config() {
        self::$log->LogDebug("Verification de la configuration de Sympa-remote");
        foreach (self::$config->get_config_vars() as $name => $value) {
            self::$log->LogDebug("Verification du parametre $name");
            self::raise_exception_if_param_not_defined($name);
            self::raise_exception_if_bad_value($name);
        }
        self::$log->LogDebug("Verification de la configuration de Sympa-remote OK");
    }

    /**
     * Teste la présence du parametre dans la configuration du 
     * si le parametre est present, on continue
     * si le parametre est absent, une exception est lancee
     * @param <type> $param le parametre a tester
     */
    private static function raise_exception_if_param_not_defined($param) {
        // Attention subtilite technique : on n'utilise pas ISSET ici car retourne toujours false meme si definie
        if (!self::$config->$param) {
            $message = "INTERNAL ERROR : parametre '$param' manquant";
            self::$log->LogError($message);
            throw new SympaRemoteBadConfigurationException("INTERNAL ERROR");
        }
    }

    /**
     * Teste si la valeur du parametre est une valeur correcte
     * si le parametre est mal configure, une exception est lancee
     * @param <type> $param le parametre a tester
     */
    private static function raise_exception_if_bad_value($param) {
        $error = false;
        if (strcmp($param,'debug_level')==0) {
            /**** Niveau de log du logger (DEBUG, INFO, WARN, ERROR, FATAL, OFF) ****/
            $authorized_values=array('debug','info','warn','error','fatal','off');
            if (!self::check_value_in_array_for_param(self::$config->$param, $authorized_values)) {
                $error = true;
                $details = "les valeurs autorisees sont DEBUG INFO WARN ERROR FATAL et OFF (Valeur trouvee = ".self::$config->$param.")";
            }
        }
        elseif (strcmp($param,'sympa_bin_dir')==0) {
            $value = self::$config->$param;
            if ($value[strlen($value)-1] != '/') {
                $error = true;
                $details = "un '/' final est necessaire (Valeur trouvee = ".$value.")";
            }
        }
        elseif (strcmp($param,'param_method')==0) {
            $authorized_values=array('post','get','both');
            if (!self::check_value_in_array_for_param(self::$config->$param, $authorized_values)) {
                $error = true;
                $details = "les valeurs autorisees sont POST GET et BOTH (Valeur trouvee = ".self::$config->$param.")";
            }
        }
        if ($error == true) {
            $message = "INTERNAL ERROR : le parametre '$param' a une valeur non supportee\n$details";
            self::$log->LogError($message);
            throw new SympaRemoteBadConfigurationException("INTERNAL ERROR");
        }
    }

    /**
     * Permet de tester que le parametre a bien une valeur autorisee
     * Retourne true si la valeur du parametre est bien une valeur autorisee,
     * false dans le cas contraire
     * @param <type> $param le nom du parametre
     * @param <type> $authorized_values le tableau de valeurs autorisees
     * @return <type> booleen
     */
    private static function check_value_in_array_for_param($param_value,$authorized_values) {
        return in_array(strtolower($param_value), $authorized_values);
    }

}

?>
