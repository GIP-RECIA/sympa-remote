<?php

/**
 * @Class ParamsWrapper
 *
 * @Purpose: Cette classe se charge de fabriquer, a partir des parametres d'entree ,les parametres requis pour la creation
 * de liste (ceux a ecrire dans le XML d'instanciation, et ceux a fournir dans la ligne de
 * commande de 'sympa.pl').
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 *
 */

class ParamsWrapper {

    /**** Logger ****/
    private $log = null;

    /**** Input Parameters ****/
    private $input_params = null;

    /**** Output Parameters for XML ***/
    private $output_params = null;

    /**** Configuration ****/
    private $config = null;

    /*** Types de listes connues ****/
    private $known_list_types = null;

    /**
     * Constructeur
     */
    public function __construct() {
        //$this->ldap_con = $ldap_connection;
        $this->log = $GLOBALS['logger'];
        $this->config = $GLOBALS['config'];
        // Verification des parametres internes necessaires au fonctionnement du ParamsWrapper
        if (strcmp($this->config->param_method, "POST")==0) {
            $this->input_params = $_POST;
        }
        elseif (strcmp($this->config->param_method, "GET")==0) {
            $this->input_params = $_GET;
        }
        elseif (strcmp($this->config->param_method, "BOTH")==0) {
            if (count($_POST) > 0) {
                // Si on trouve des parametres en POST, ils prennent le dessus
                $this->input_params = $_POST;
            }
            else {
                $this->input_params = $_GET;
            }
        }
        $this->log->LogDebug("ParamsWrapper : Params = " . print_r($this->input_params, true));
        $this->loadListTypes();
    }

    /**
     * Verification des parametres d'entree du service
     */
    public function check() {
	$operation = $this->input_params[SympaRemoteConstants::INPUT_OPERATION];

	if ($operation == "CREATE" || $operation == "UPDATE") {
	    $this->checkCreateOrUpdateOperation();
	} else if ($operation == "CLOSE") {
	    $this->checkCloseOperation();
	} else {
	    $this->log->LogError("ParamsWrapper : Operation [$operation] not supported !");
	    throw new ParamsWrapperCheckException('OPERATION_NOT_SUPPORTED',3);
    	exit(1);
	}
    }

    private function checkCloseOperation() {
        $this->log->LogDebug("ParamsWrapper : Verification des parametres d'entree pour l'operation CLOSE...");
        if (!is_array($this->input_params)) {
            $this->log->LogError("NOT_ENOUGH_PARAMETERS");
            throw new ParamsWrapperCheckException('MISSING_PARAMETER(S)',1);
            exit(1);
        }
	
		/*
 		 * TEST DE PRESENCE DES PARAMETRES OBLIGATOIRES
 		 */
		$this->log->LogDebug("ParamsWrapper : Test de presence des parametres obligatoires");
		$this->checkMissingParameter(SympaRemoteConstants::INPUT_LIST_NAME_TO_CLOSE);

		/*
 		 * CONTROLE DE LA VALEUR DES PARAMETRES OBLIGATOIRES
		 */
		$this->log->LogDebug("ParamsWrapper : Test des valeurs des parametres obligatoires");
		$this->checkParameterValue(SympaRemoteConstants::INPUT_LIST_NAME_TO_CLOSE);

    }

    private function checkCreateOrUpdateOperation() {
        $this->log->LogDebug("ParamsWrapper : Verification des parametres d'entree pour l'operation CREATE or UPDATE...");
        if (!is_array($this->input_params)) {
            $this->log->LogError("NOT_ENOUGH_PARAMETERS");
            throw new ParamsWrapperCheckException('MISSING_PARAMETER(S)',1);
            exit(1);
        }
	
		/*
		 * TEST DE PRESENCE DES PARAMETRES OBLIGATOIRES
		 */
		$this->checkMissingParameter(SympaRemoteConstants::INPUT_OPERATION);
		$this->log->LogDebug("ParamsWrapper : Test de presence des parametres obligatoires");
		$this->checkMissingParameter(SympaRemoteConstants::INPUT_LIST_TYPE);
		$this->checkMissingParameter(SympaRemoteConstants::INPUT_RNE);
		$this->checkMissingParameter(SympaRemoteConstants::INPUT_WRITING_POLICY);

		/*
		 * CONTROLE DE LA VALEUR DES PARAMETRES OBLIGATOIRES
		 */
		$this->log->LogDebug("ParamsWrapper : Test des valeurs des parametres obligatoires");
		$this->checkParameterValue(SympaRemoteConstants::INPUT_OPERATION);
		$this->checkParameterValue(SympaRemoteConstants::INPUT_LIST_TYPE);
		$this->checkParameterValue(SympaRemoteConstants::INPUT_WRITING_POLICY);

		/*
		 * TEST DE PRESENCE DES PARAMETRES SPECIFIQUES A CERTAINES VALEURS
		 * ET TESTS DES VALEURS AUTORISEES
		 */
		$this->log->LogDebug("ParamsWrapper : Test de presence des parametres specifiques");
		if ($this->isListTypeNeedParameter($this->input_params[SympaRemoteConstants::INPUT_LIST_TYPE])) {
		    // Test de presence du parametre specifique a certains types de listes
 		    $this->log->LogDebug("ParamsWrapper : le modele choisi necessite un parametre");
		    $this->checkMissingParameter(SympaRemoteConstants::INPUT_LIST_TYPE_PARAMETER);
		} else {
    		$this->log->LogDebug("ParamsWrapper : le modele choisi ne necessite pas de parametre");
		}

		// Si aucun alias d'editeur n'est fourni, sympa-remote va utiliser
		// les requetes (alias) marquees comme MANDATORY pour le model correspondant (BD)
		// Si certains sont fournis, on controle qu'ils sont bien connus
		$this->checkParameterValue(SympaRemoteConstants::INPUT_EDITORS_ALIASES);

    	// On ne verifie pas les groupes fournis.
    	$this->log->LogDebug("ParamsWrapper : Verification des parametres d'entree OK");
    }

    /**
     * Fonction permettant de convertir les parametres d'entree au format attendu
     * par l'executable de Sympa (sympa.pl)
     */
    public function wrap() {
        $output_params = array();
		// Operation
		$operation = $this->input_params[SympaRemoteConstants::INPUT_OPERATION];
		$this->output_params[SympaRemoteConstants::INPUT_OPERATION] = $operation;

		if ($operation == "CREATE" || $operation == "UPDATE") {
	    	$this->wrapCreateOrUpdateOperation();	
		} else if ($operation == "CLOSE") {
	    	$this->wrapCloseOperation();	
		} else {
	    	$this->log->LogError("ParamsWrapper : Operation [$operation] not supported !");
	    	throw new ParamsWrapperCheckException('OPERATION_NOT_SUPPORTED',3);
    	   	exit(1);
		}
	
    }

    private function wrapCloseOperation() {
        $this->log->LogDebug("ParamsWrapper : Transformation des parametres pour sympa.pl pour l'operation CLOSE...");
        $output_params = array();

		$this->output_params[SympaRemoteConstants::INPUT_LIST_NAME_TO_CLOSE] = $this->input_params[SympaRemoteConstants::INPUT_LIST_NAME_TO_CLOSE];
    }

    private function wrapCreateOrUpdateOperation() {
        $this->log->LogDebug("ParamsWrapper : Transformation des parametres pour sympa.pl pour l'operation CREATE ou UPDATE...");
        $output_params = array();

        /*
         * Recuperation du modele correspondant au type de liste demande et preparation des parametres
         */
        $type_liste = $this->input_params[SympaRemoteConstants::INPUT_LIST_TYPE];

        /*
         * Preparation des parametres qui pourront etre remplaces
         * dans les chaines (proprietes et filtres de groupes)
         */
        $parametre_du_type = false;
        // On fabrique le token {UAI} avec sa valeur, (il n'a pas besoin d'etre fourni par l'utilisateur, puisqu'il est fourni � part)
        $tokens = array(strtoupper(SympaRemoteConstants::INPUT_RNE) => strtoupper($this->input_params[SympaRemoteConstants::INPUT_RNE]),
        strtoupper(SympaRemoteConstants::INPUT_SIREN) => strtoupper($this->input_params[SympaRemoteConstants::INPUT_SIREN]));
	
        // On ne traite le parametre de modele que si celui-ci est requis par le modele choisi
        if ($this->isListTypeNeedParameter($this->input_params[SympaRemoteConstants::INPUT_LIST_TYPE])) {
            if (array_key_exists(SympaRemoteConstants::INPUT_LIST_TYPE_PARAMETER, $this->input_params)) {
                // Recuperation du parametre de type/modele facultatif (CLASSE ou NIVEAU...)
                $parametre_du_type = $this->input_params[SympaRemoteConstants::INPUT_LIST_TYPE_PARAMETER];
                $this->log->LogDebug("ParamsWrapper : Parametre TypeParameter = $parametre_du_type");
                $param = explode("$", $parametre_du_type); // param[0] => nom du parametre et [1] sa valeur
                $token_param = array($param[0] => $param[1]);
                $tokens[$param[0]] = $param[1];
                $this->log->LogDebug("ParamsWrapper : Tokens utilisables dans les groupes = ".implode('/', $tokens));
            }
        }

        /***
         * Construction de tous les parametres de sortie (pour le XML d'instanciation de liste)
         ***/
        // PARAMETRE 'NOM DE LA FAMILLE' DANS LAQUELLE CREER LA LISTE
        // On recupere la propriete contenant le nom de la famille dans laquelle ajouter la liste
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".SympaPLClient::ARGUMENT_FAMILLE);
        $famille = $this->known_list_types->getModeleProperties($type_liste, ListTypes::FAMILLE);
        $this->output_params[SympaPLClient::ARGUMENT_FAMILLE]=$famille;
        $this->log->LogDebug("ParamsWrapper : Parametre ".SympaPLClient::ARGUMENT_FAMILLE." = $famille");

        // PARAMETRE 'LISTNAME'
        // On recupere la propriete contenant le nom de la liste configuree dans le modele "type_liste" qui est le modele souhaite
        // Attention le listname peut contenir des parametres a remplacer... {CLASSE} .. etc
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_LISTNAME);
        $listname = $this->known_list_types->getModeleProperties($type_liste, ListTypes::NOM_LISTE);
        // Si le parametre facultatif du type est defini, on tente de remplacer dans le listname
        if ($parametre_du_type) {
            $listname = ArgumentFiller::getFilledString($listname, $tokens);
        }
		$listname = ArgumentFiller::strip_string($listname);
        $this->output_params[XMLBuilder::XML_TAG_LISTNAME] = $listname;
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_LISTNAME." = $listname");

        // PARAMETRE 'SUBJECT'
        // On recupere aussi la propriete du model
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_SUBJECT);
        $subject = $this->known_list_types->getModeleProperties($type_liste, ListTypes::SUJET);
        if ($parametre_du_type) {
            $subject = ArgumentFiller::getFilledString($subject, $tokens);
        }
        $this->output_params[XMLBuilder::XML_TAG_SUBJECT] = $subject;
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_SUBJECT." = $subject");

        // PARAMETRE 'TOPICS'
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_TOPICS);
        $topics = $this->known_list_types->getModeleProperties($type_liste, ListTypes::CATEGORIE);
        $this->output_params[XMLBuilder::XML_TAG_TOPICS] = $topics;
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_TOPICS." = $topics");

        // PARAMETRE 'DESCRIPTION'
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_DESCRIPTION);
        $description = $this->known_list_types->getModeleProperties($type_liste, ListTypes::DESC);
        if ($parametre_du_type) {
            $description = ArgumentFiller::getFilledString($description, $tokens);
        }
        $this->output_params[XMLBuilder::XML_TAG_DESCRIPTION] = $description;
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_DESCRIPTION." = $description");


        // PARAMETRE ECRIVAINS CONNUS (requetes pre-definies) DE LA LISTE
        // On s'occupe des alias d'inclusion d'ecrivains fournis par l'utilisateur
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_EDITORS_FROM_REQUEST);
        $aliases = array();
        if ($this->input_params[SympaRemoteConstants::INPUT_EDITORS_ALIASES]) {
            $editors_alias = explode('$',$this->input_params[SympaRemoteConstants::INPUT_EDITORS_ALIASES]);
            if (is_array($editors_alias) && $editors_alias[0]!="") {
                // Si des identifiants de requetes d'editeurs (ce que j'appelle alias) ont ete fournis
                foreach($editors_alias as $id_request) {
                    // Pour chaque identifiant d'alias, on recupere la requete ldap associee et on la stocke dans le tableau
                    // On creer un tableau dont les identifiants des alias sont les cles du tableau
                    $request=$this->known_list_types->getEditorRequestWithId($id_request);
                    $aliases[$id_request]=ArgumentFiller::getEscapedFilledString($request['ldapfilter'],$tokens);
                }
            }
        }

        // On recupere les requetes d'inclusion d'editeurs obligatoires pour ce modele
        $temp_mandatory_editors_alias = $this->known_list_types->getMandatoryEditorsRequestsForModel($type_liste);
        $this->log->LogDebug("ParamsWrapper : Ajout des ecrivains obligatoires (requetes) si besoin");
        foreach($temp_mandatory_editors_alias as $id_request => $request) {
            // On ajoute tous les editeurs obligatoires qui n'ont pas ete fournis en parametres.
            if (!array_key_exists($id_request, $aliases)) {
                $aliases[$id_request]=ArgumentFiller::getEscapedFilledString($request['ldapfilter'],$tokens);
            }
        }
        if (count($aliases)==0) {
            //$this->log->LogWarn("ParamsWrapper : ATTENTION personne ne pourra ecrire sur cette liste (aucune requete definissant les ecrivains n'a ete fournie, et aucune obligatoire pour ce modele)");
            $this->log->LogError("ParamsWrapper : Impossible de creer une liste sans ecrivain (pas d'ecrivains obligatoires trouves).");
            throw new SympaRemoteBadConfigurationException("NO_EDITORS",1);
            exit(1);
        }
        $this->output_params[XMLBuilder::XML_TAG_EDITORS_FROM_REQUEST] = $aliases;
        $string="";
        foreach($aliases as $filter) {
	    $string = $string."\n=>".$filter;
        }
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_EDITORS_FROM_REQUEST." = $string");

        // PARAMETRE GROUPES D'ECRIVAINS FOURNIS PAR L'UTILISATEUR
        // On recupere les groupes fournis par l'utilisateur et on les ajoute
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_EDITORS_FROM_GROUP);
        $editors_groups = array();
        if ($this->input_params[SympaRemoteConstants::INPUT_EDITORS_GROUPS]) {
            $user_groups = explode('$',$this->input_params[SympaRemoteConstants::INPUT_EDITORS_GROUPS]);
            if ($user_groups[0] != "") {
                foreach($user_groups as $groupname) {
                    $group_found = GroupSearcher::search(ArgumentFiller::getEscapedFilledString($groupname, $tokens));
                    if ($group_found != false) {
                        array_push($editors_groups,$group_found);
                    }
                }
            }
            $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_EDITORS_FROM_GROUP." = ".implode('/',$editors_groups));
        }
        else {
            $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_EDITORS_FROM_GROUP." = Aucun groupe d'editeurs fourni");
        }
        $this->output_params[XMLBuilder::XML_TAG_EDITORS_FROM_GROUP] = $editors_groups;


        // PARAMETRE SCENARIO D'ECRITURE
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_SCENARIO_SEND);
        $this->output_params[XMLBuilder::XML_TAG_SCENARIO_SEND] = $this->input_params[SympaRemoteConstants::INPUT_WRITING_POLICY];
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_SCENARIO_SEND." = ".$this->output_params[XMLBuilder::XML_TAG_SCENARIO_SEND]);

        // PARAMETRE 'GROUPE DES PROPRIETAIRES (UN SEUL GROUPE, PARAMETRE DANS LA CONFIG DE SYMPA-REMOTE, LE MEME POUR TOUTES LES CREATIONS)
        // Le groupe des proprietaires est un filtre, fourni dans le fichier de parametrage.
        // On remplace les tokens du filtre, et on fait la recherche LDAP pour recuperer le nom du groupe,
        // comme pour les groupes d'editeurs
        // NB : seul le token {RNE} est autorise pour le groupe des proprietaires
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_OWNERS_GROUP);
        $owners_group = GroupSearcher::search(ArgumentFiller::getEscapedFilledString($this->config->owners_group_filter, $tokens));
        if ($owners_group == false) {
            $this->log->LogError("ParamsWrapper : Impossible de trouver un groupe de proprietaires avec le filtre\n".$this->config->owners_group_filter."\nVerifier la configuration de la propriete ".$owners_group_filter. " dans config.inc.php");
            throw new SympaRemoteBadConfigurationException("NO_OWNERS",1);
            exit(1);
        }
        $this->output_params[XMLBuilder::XML_TAG_OWNERS_GROUP] = $owners_group;
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_OWNERS_GROUP." = ".$owners_group);

        // PARAMETRE 'GROUPE DES ABONNES
        // C'est aussi un filtre, fourni dans le parametrage du modele de liste concerne.
        // Meme fonctionnement que pour le groupe des proprietaires, on remplace les tokens si besoin...
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".XMLBuilder::XML_TAG_SUBSCRIBERS_GROUP);
        $subscribers_prop = $this->known_list_types->getModeleProperties($type_liste, ListTypes::ABONNES);
        if ($subscribers_prop != "") {
            $subscribers_group = GroupSearcher::search(ArgumentFiller::getEscapedFilledString($subscribers_prop, $tokens));
            if ($subscribers_group == false) {
                $this->log->LogError("ParamsWrapper : Aucun groupe d'abonne n'a etre trouve avec le filtre '\n".$this->known_list_types->getModeleProperties($type_liste, ListTypes::ABONNES)."'\nChanger le filtre dans la base de donnees des modeles");
                throw new SympaRemoteBadConfigurationException("NO_SUBSCRIBERS",1);
                exit(1);
            }
        }
        else {
            $this->log->LogError("ParamsWrapper : Aucun filtre d'abonnee defini pour le modele '$type_liste'");
            throw new SympaRemoteBadConfigurationException("NO_SUBSCRIBERS",1);
            exit(1);
        }

        $this->output_params[XMLBuilder::XML_TAG_SUBSCRIBERS_GROUP] = $subscribers_group;
        $this->log->LogDebug("ParamsWrapper : Parametre ".XMLBuilder::XML_TAG_SUBSCRIBERS_GROUP." = ".$subscribers_group);

        // PARAMETRE RNE
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".SympaRemoteConstants::INPUT_RNE);
        $this->output_params[SympaRemoteConstants::INPUT_RNE]=$this->input_params[SympaRemoteConstants::INPUT_RNE];
        $this->log->LogDebug("ParamsWrapper : Parametre ".SympaRemoteConstants::INPUT_RNE." = ".$this->input_params[SympaRemoteConstants::INPUT_RNE]);

        // PARAMETRE SIREN
        $this->log->LogDebug("ParamsWrapper : Construction du parametre ".SympaRemoteConstants::INPUT_SIREN);
        $this->output_params[SympaRemoteConstants::INPUT_SIREN]=$this->input_params[SympaRemoteConstants::INPUT_SIREN];
        $this->log->LogDebug("ParamsWrapper : Parametre ".SympaRemoteConstants::INPUT_SIREN." = ".$this->input_params[SympaRemoteConstants::INPUT_SIREN]);

        $this->log->LogDebug("ParamsWrapper : Fin de Transformation des parametres pour sympa.pl");
    }

    /**
     * Retourne un parametre transforme pret a etre utilise
     * @param <type> $param_name Nom du parametre
     */
    public function getWrappedParameter($param_name) {
        return $this->output_params[$param_name];
    }

    /**
     * Retourne le tableau de tous les parametres transformes
     */
    public function getWrappedParameters() {
        return $this->output_params;
    }

    /**
     * Fonction permettant de lancer une exception si un parametre d'entree requis
     * est manquant.
     * @param <type> $parameter parametre a tester
     */
    private function checkMissingParameter($parameter) {
        if (!isset($this->input_params[$parameter]) || $this->input_params[$parameter]=="") {
            $this->log->LogError("ParamsWrapper : $parameter obligatoire mais non fourni");
            throw new ParamsWrapperCheckException('MISSING_PARAMETER(S)',1);
            exit(1);
        }
    }

    /**
     * Fonction permettant de lancer une exception si la valeur d'un parametre n'est pas bonne
     * @param <type> $parameter Le parametre d'entree
     * @param <type> $value La valeur fournie par l'utilisateur
     */
    private function checkParameterValue($parameter) {
        if (strcmp($parameter,SympaRemoteConstants::INPUT_LIST_TYPE) == 0) {
            // On s'assure que le type de liste demandee fait bien partie des modeles connus
            if (!array_key_exists($this->input_params[SympaRemoteConstants::INPUT_LIST_TYPE], $this->known_list_types->getModeles())) {
                $this->log->LogError("ParamsWrapper : ".$this->input_params[SympaRemoteConstants::INPUT_LIST_TYPE]." n'est pas une valeur correcte pour le parametre ".SympaRemoteConstants::INPUT_LIST_TYPE);
                throw new ParamsWrapperCheckException('UNKNOWN_LIST_TYPE',3);
                exit(1);
            }
        }
        else if (strcmp($parameter,SympaRemoteConstants::INPUT_OPERATION) == 0) {
            // MBD: ajout d'une operation => liste des operation est dorenavant un array
	    	if (!in_array($this->input_params[SympaRemoteConstants::INPUT_OPERATION], SympaRemoteConstants::$OPERATION_CREATION_LISTE)) {
                $this->log->LogError("ParamsWrapper : ".$this->input_params[SympaRemoteConstants::INPUT_OPERATION]." n'est pas une valeur correcte pour le parametre ".SympaRemoteConstants::INPUT_OPERATION);
                throw new ParamsWrapperCheckException('OPERATION_NOT_SUPPORTED',3);
                exit(1);
            }
        }
        else if (strcmp($parameter,SympaRemoteConstants::INPUT_WRITING_POLICY) == 0) {
            if (!$this->isAuthorizedScenario()) {
                $this->log->LogError("ParamsWrapper : ".$this->input_params[SympaRemoteConstants::INPUT_WRITING_POLICY]." n'est pas une politique autorisee dans config.inc.php");
                throw new ParamsWrapperCheckException('UNAUTHORIZED_POLICY',2);
                exit(1);
            }
        }
        else if (strcmp($parameter,SympaRemoteConstants::INPUT_EDITORS_ALIASES) == 0) {
            $editors_aliases = explode('$',$this->input_params[SympaRemoteConstants::INPUT_EDITORS_ALIASES]);
            if (!$editors_aliases[0] == "") {
                $this->log->LogDebug("ParamsWrapper : controle des alias d'editeurs fournis");
                foreach($editors_aliases as $alias) {
                    if (!$this->known_list_types->isExistingPreparedRequest($alias)) {
                        $this->log->LogError("ParamsWrapper : la requete preparee ".$this->input_params[SympaRemoteConstants::INPUT_EDITORS_ALIASES]);
                        throw new ParamsWrapperCheckException('UNKNOWN_EDITORS',3);
                        exit(1);
                    }
                }
            }
            else {
                $this->log->LogDebug("ParamsWrapper : aucun alias d'editeur fourni");
            }
        }
    }

    /**
     * Fonction permettant de savoir si on est dans le cas d'une politique d'ecriture autorisant
     * seulement les editeurs/moderateurs de liste a ecrire a la liste.
     * @return <type> booleen Vrai si la politique d'ecriture est la politique EDITEURS_SEULEMENT
     */
    private function isAuthorizedScenario() {
        $result = true;
        if (strpos($this->config->authorized_send_scenario,"$") != false) {
            $authorized_scenari = explode('$', $this->config->authorized_send_scenario);
            if (!array_search($this->input_params[SympaRemoteConstants::INPUT_WRITING_POLICY], $authorized_scenari)) {
                $result = false;
            }
        }
        else {
            if (!strcmp($this->config->authorized_send_scenario, $this->input_params[SympaRemoteConstants::INPUT_WRITING_POLICY]) == 0) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Fonction permettant de savoir si le modele de liste en question requiert un parametre
     * pour pouvoir instancier la liste (CLASSE, NIVEAU...)
     * Attention : un seul parametre est actuellement supporte
     * @param <type> $type le typ
     * @return <type> true si le modele requiert un parametre, false autrement.
     */
    private function isListTypeNeedParameter($type) {
        return $this->known_list_types->getModeleProperties($type, ListTypes::PARAMETRE_REQUIS);
    }

    /**
     * Fonction permettant de charger depuis la base de donnee, grace
     * a la classe ListTypes, les differents modeles de liste pour lesquels
     * Sympa-remote peut creer des listes.
     */
    private function loadListTypes() {
        /*
         * Chargement des types de listes possibles
         */
        try {
    	    $databseId = $this->input_params[SympaRemoteConstants::INPUT_DATABASE_ID];
            $this->known_list_types = new ListTypes($databseId);
        }
        catch(ListTypesDirectoryNotFoundException $e) {
            $this->log->LogError("ParamsWrapper : BAD CONFIGURATION \n$e");
            throw new SympaRemoteBadConfigurationException("BAD CONFIGURATION",1);
            exit(1);
        }
    }
}

?>
