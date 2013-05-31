<?php

/**
 * @Class
 *
 * @Purpose: Classe principale de Sympa-remote rendant le service de creation de liste
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

class SympaRemoteCore {

    /* logger */
    private $log = null;

    /* Objet gerant les parametres d'entree */
    private $params_wrapper = null;

    /* connexion au ldap */
    private $ldap = null;

    /* Configuration du batch */
    private $config = null;

    /**
     * Fonction realisant l'enchainement des actions qui constituent le service rendu
     */
    public function do_service() {
        $this->params_wrapper->check();
        $this->params_wrapper->wrap();
        $xml_content = XMLBuilder::buildXML($this->params_wrapper->getWrappedParameters());
        $sympa_client = new SympaPLClient();
        $main_robot_name = strtolower($this->params_wrapper->getWrappedParameter(SympaRemoteConstants::INPUT_RNE)).".".$this->config->sympa_main_domain;

		$operation = $this->params_wrapper->getWrappedParameter(SympaRemoteConstants::INPUT_OPERATION);
		$this->log->LogDebug("Operation : " . $operation);
		switch ($operation) {
			case "CREATE":
				$sympa_client->createListWithXML($xml_content, $this->params_wrapper->getWrappedParameter(SympaPLClient::ARGUMENT_FAMILLE), $main_robot_name);
				break;
			case "UPDATE":
				$sympa_client->updateListWithXML($xml_content, $this->params_wrapper->getWrappedParameter(SympaPLClient::ARGUMENT_FAMILLE), $main_robot_name);
				break;
			case "CLOSE":
				$listname = $this->params_wrapper->getWrappedParameter(SympaRemoteConstants::INPUT_LIST_NAME_TO_CLOSE);
				$sympa_client->closeList($listname);
				break;
		}
    }		




    /**
     * @Constructor Constructeur de la classe SympaRemoteCore
     */
    public function __construct() {
        $this->initialize();
        $this->params_wrapper = new ParamsWrapper();
    }

    /*
     * Initialisation technique
     *  - Chargement du fichier de configuration
     *  - Initialisation du logger
     */
    private function initialize() {
        // Lecture de la config du batch
        $this->load_config();
        $GLOBALS['config'] = $this->config;
        // Initialisation du logger
        if (strcasecmp("DEBUG", $this->config->debug_level) == 0) {
            $debug_const = KLogger::DEBUG;
        }
        else if(strcasecmp("INFO", $this->config->debug_level) == 0) {
            $debug_const = KLogger::INFO;
        }
        else if(strcasecmp("WARN", $this->config->debug_level) == 0) {
            $debug_const = KLogger::WARN;
        }
        else if(strcasecmp("ERROR", $this->config->debug_level) == 0) {
            $debug_const = KLogger::ERROR;
        }
        else if(strcasecmp("FATAL", $this->config->debug_level) == 0) {
            $debug_const = KLogger::FATAL;
        }
        else if(strcasecmp("OFF", $this->config->debug_level) == 0) {
            $debug_const = KLogger::OFF;
        }
        $this->log = new KLogger ( $this->config->log_file , $debug_const );
        $GLOBALS['logger'] = $this->log;
        $this->check_conf();
        $this->log->LogDebug("Init...OK");

    }

    /*
     * Connexion LDAP
     */
    private function initialize_ldap_connection() {
        try {
            $this->ldap = new LDAPServer();
        }
        catch(Exception $e) {
            $message = "Erreur de connexion au LDAP (mauvais parametrage ?) : \n".$e->getMessage();
            $this->log->LogFatal($message);
            throw new Exception($message);
        }
    }

    /*
     * Permet de s'assurer que le connection LDAP est disponible,
     * et qu'on peut recuperer le SIREN de l'etablissement
     */
    private function check_ldap_connection() {
        $this->log->LogInfo("Verification de la connexion LDAP");
        // A FAIRE SI BESOIN
    }

    /**
     * Chargement de la config si necessaire
     */
    private function load_config() {
        $this->config = new Config();
    }

    /**
     * Verification de la configuration chargee
     */
    private function check_conf() {
        ConfigChecker::check();
    }

}

?>
