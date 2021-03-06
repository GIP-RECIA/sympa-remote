<?php

/**
 * @Class ListTypes
 *
 * @Purpose: Classe permettant de charger et stocker les types de listes qu'il est
 * possible de creer (listes pour lesquelles on a des instructions de creation)
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 * Classe permettant de charger et stocker les types de listes qu'il est
 * possible de creer
 *
 */

class ListTypes {

    /* Noms des champs utilises */
    /* Proprietes qui doivent etres definies dans les modeles */
    const NOM_LISTE = "listname";
    const DESC = "description";
    const CATEGORIE = "topics";
    const SUJET = "subject";
    const FAMILLE = "family";
    const PARAMETRE_REQUIS = "need_parameter";
    const CATEGORIE_EDITEUR = "category";
    const EDITEURS_OBLIGATOIRES = "MANDATORY";
    const EDITEURS_COCHES = "CHECHKED";
    const EDITEURS_NON_COCHES = "UNCHECKED";
    const ABONNES = "subscribers_group"; // Attention : UN SEUL GROUPE !! car sympa ne sait pas faire plusieurs includes
    // La solution pour inclure plusieurs groupes d'abonnes sera de mettre un filtre LDAP.

    /**** Logger ****/
    private $log = null;

    /**** Configuration ****/
    private $config = null;

    /**** Les modeles de listes ****/
    private $modeles = null;

    /**
     * @Constructor Constructeur de la classe ListTypes
     */
    public function __construct($dbIndex = "Default") {
        $this->log = $GLOBALS['logger'];
        $this->config = $GLOBALS['config'];
        $this->load_list_types($dbIndex);
    }

 
    /**
     * Fonction permettant de charger depuis la base de donnees les modeles de listes
     * que sympa-remote va etre capable de creer
     */
    private function load_list_types($dbIndex) {
        $this->log->LogDebug("ListTypes : Chargement des types de listes connus");
        $nb_models = 0;
        $this->modeles = array();
        $con = $this->connect_to_db($dbIndex);
        // On recupere les modeles de listes
        $sql = "SELECT * FROM model";
        if (!$req = mysqli_query($con, $sql)) {
            $this->log->LogError("ListTypes: Impossible d'effectuer la requete\n".$sql .mysqli_error());
            throw new ListTypesNoModelsFoundException("can't make the request (table not found ?)",2);
            exit(1);
        }
        if ($nb_models = mysqli_num_rows($req)) {
            while ($data = mysqli_fetch_assoc($req)) {
                $this->modeles[$data['modelname']]=$data;
                $this->log->LogDebug("ListTypes : Chargement du modele ".$data['modelname']);
            }
            mysqli_free_result($req);
        }
        mysqli_close($con);
        if ($nb_models == 0) {
            $this->log->LogError("ListTypes : Aucun type de liste connu");
            throw new ListTypesNoModelsFoundException("no models can be found",1);
            exit(1);
        }
        // Le tableau des modeles est cree
        $this->log->LogDebug("ListTypes : Chargement des types de listes connus OK");
    }

    /**
     * Permet de recuperer le tableau contenant les modeles
     */
    public function getModeles() {
        return $this->modeles;
    }

    /**
     * Permet de recuperer une propriete d'un modele
     * @param <type> $model_name Le nom du modele
     * @param <type> $property La propriete souhaitee
     * @return la valeur de la propriete : peut être un tableau de valeur (pour les editors notamment)
     */
    public function getModeleProperties($model_name, $property, $dbIndex) {
        if (strcmp($property,ListTypes::NOM_LISTE) == 0
            || strcmp($property,ListTypes::DESC) == 0
            || strcmp($property,ListTypes::CATEGORIE) == 0
            || strcmp($property,ListTypes::SUJET) == 0
            || strcmp($property,ListTypes::PARAMETRE_REQUIS) == 0
            || strcmp($property,ListTypes::FAMILLE) == 0) {
            // Proprietes deja chargees depuis la BD, pas de connexion necessaire
            $value = $this->modeles[$model_name][$property];
        }
        else if (strcmp($property,ListTypes::EDITEURS_OBLIGATOIRES) == 0
            || strcmp($property,ListTypes::EDITEURS_COCHES) == 0
            || strcmp($property,ListTypes::EDITEURS_NON_COCHES) == 0 ) {
            // Sinon, c'est qu'on veut obtenir des informations que l'on a pas encore recupere en BD
            //MADE $value devient ici un tableau à trois valeurs. 
            $value = $this->getModelEditors($model_name, $property, $dbIndex);
        }
        else if (strcmp($property,ListTypes::ABONNES) == 0) {
            $value = $this->getModelSubscribers($model_name, $dbIndex);
        }
        else {
            $this->log->LogError("ListTypes : Propriete demandee non connue");
            throw new ListTypesUnknownProperty("property $property is not a model property",1);
            exit(1);
        }
        return $value;
    }

    /**
     * Connexion a la base de donnees
     * @return <type>
     */
    private function connect_to_db($dbIndex) {
	    $host = Config::get_array_value($this->config->db_host, $dbIndex);
		$user = Config::get_array_value($this->config->db_user, $dbIndex);
		$pass = Config::get_array_value($this->config->db_pass, $dbIndex);
		$db = Config::get_array_value($this->config->db_db, $dbIndex);
		$this->log->LogDebug("ListTypes : database connexion informations are: host: $host ; user: $user ; db: $db");
        
        $con = mysqli_connect($host, $user, $pass);
        if ($con == false) {
            $this->log->LogError("ListTypes : Impossible de se connecter a la base des modeles de listes ".mysqli_error());
            throw new ListTypesNoModelsFoundException("can't connect to database",2);
            exit(1);
        }
        if (!mysqli_select_db($con, $db)) {
            $this->log->LogError("ListTypes : Impossible de trouver la base ".$db);
            throw new ListTypesNoModelsFoundException("No database ".$db, 2);
            exit(1);
        }
        return $con;
    }

    /**
     * Fonction permettant de recuperer la requete preparee ayant pour identifiant l'identifiant fourni.
     * retourne la requete dans un tableau contenant une cle "display_name" et une cle "ldapfilter".
     * @param <type> $id_request l'identifiant de requete
     */
     //MADE pierre modification de la requette pour donner la data source et le ldap suffix.
    public function getEditorRequestWithId($id_request, $dbIndex) {
        $the_request = array();
        $con = $this->connect_to_db($dbIndex);
        $sql = "SELECT display_name, ldapfilter, data_source, ldap_suffix FROM prepared_request WHERE id_request=$id_request";
        if (!$req = mysqli_query($con, $sql)) {
            $this->log->LogError("ListTypes : Impossible d'effectuer la requete\n".$sql .mysqli_error());
            throw new ListTypesSQLException("can't make the request (table not found ?)",2);
            exit(1);
        }
        if ($nb_req = mysqli_num_rows($req)) {
            $prepared_request = mysqli_fetch_assoc($req);
            $the_request['display_name']=$prepared_request['display_name'];
            $the_request['ldapfilter']=$prepared_request['ldapfilter'];
             $the_request['source']=$prepared_request['data_source'];
            $the_request['ldapsuffix']=$prepared_request['ldap_suffix'];
            $this->log->LogDebug("ListTypes : requete editeur avec id $id_request trouvee : ".implode("/",$the_request));
            mysqli_free_result($req);
        }
        mysqli_close($con);
        return $the_request;
    }

    /**
     * Fonction permettant de recuperer les requetes obligatoires pour un model
     * retourne la requete dans un tableau contenant une cle "id", une cle "display_name" et une cle "ldapfilter".
     * PL : ajout de "source" et "ldapsuffix":
     * @param <type> $model_name
     */
     //MADE pierre ... ajout de source et ldapsuffix dans la tableau resultat
    public function getMandatoryEditorsRequestsForModel($model_name, $dbIndex) {
        $requests = array();
        $con = $this->connect_to_db($dbIndex);
        $sql = "SELECT id_request, display_name, ldapfilter, data_source, ldap_suffix  FROM v_model_editors WHERE modelname='$model_name' AND category='MANDATORY'";
        if (!$req = mysqli_query($con, $sql)) {
            $this->log->LogError("ListTypes : Impossible d'effectuer la requete\n".$sql .mysqli_error());
            throw new ListTypesSQLException("can't make the request (table not found ?)",2);
            exit(1);
        }
        if ($nb_req = mysqli_num_rows($req)) {
            while ($prepared_requests = mysqli_fetch_assoc($req)) {
                $id_request=$prepared_requests['id_request'];
                $requests[$id_request]=array();
                $requests[$id_request]['display_name']=$prepared_requests['display_name'];
                $requests[$id_request]['ldapfilter']=$prepared_requests['ldapfilter'];
                $requests[$id_request]['source']=$prepared_requests['data_source'];
				$requests[$id_request]['ldapsuffix']=$prepared_requests['ldap_suffix'];
                $this->log->LogDebug("ListTypes : filtre d'editeurs obligatoires : ".implode("/",$requests[$id_request]));
            }
            mysqli_free_result($req);
        }
        else {
            $this->log->LogDebug("ListTypes : Aucun editeur obligatoire trouve pour le modele $model_name");
        }
        mysqli_close($con);
        return $requests;
    }

    /**
     * Methode permettant de recuperer, pour le modele fourni en parametre,
     * les requete preparees d'editeurs de liste de la categorie demandee
     * (editeurs obligatoires, editeurs conseilles, editeurs proposes...
     * Cf. Valeurs possibles de category dans le modele de BD)
     * @param <type> $model_name Le nom du modele
     * @param <type> $editors_category la categorie d'editeurs
     * @return <type> array[array] : un tableau contenant un tableau de requetes preparees, sous la forme de (filtre LDAP, source, suffic ldap).
     */
     // MADE pierre ...
    private function getModelEditors($model_name, $editors_category, $dbIndex) {
        $editors_requests = array();
        $con = $this->connect_to_db($dbIndex);
        $sql = "SELECT * FROM v_model_editors WHERE modelname='$model_name' AND category='$editors_category'";
        if (!$req = mysqli_query($con, $sql)) {
            $this->log->LogError("ListTypes : Impossible d'effectuer la requete\n".$sql .mysqli_error());
            throw new ListTypesNoModelsFoundException("can't make the request (table not found ?)",2);
            exit(1);
        }
        if ($nb_editors = mysqli_num_rows($req)) {
            while ($editors = mysqli_fetch_assoc($req)) {
				$nuplet = array($editors['ldapfilter'], $editors['data_source'], $editors['ldap_suffix']);
				
                array_push($editors_requests, $nuplet);
                $this->log->LogDebug("ListTypes : filtre editeurs ".$nuplet. " pour le modele $model_name");
            }
            mysqli_free_result($req);
        }
        mysqli_close($con);
        return $editors_requests;
    }

    /**
     * Methode permettant de recuperer, pour le modele fourni en parametre,
     * le filtres de groupe d'abonnes associe.
     * (capable d'en retourner uniquement un pour l'instant...)
     * @param <type> $model_name le nom du modele
     * @return <type> string : le filtre de groupe trouve en base de donnees
     */
    private function getModelSubscribers($model_name, $dbIndex) {
        $group = false;
        $con = $this->connect_to_db($dbIndex);
        $sql = "SELECT group_filter FROM model,model_subscribers WHERE model_subscribers.id = model.id AND model.modelname = '$model_name'";
        if (!$req = mysqli_query($con, $sql)) {
            $this->log->LogError("ListTypes : Impossible d'effectuer la requete\n".$sql .mysqli_error());
            throw new ListTypesNoModelsFoundException("can't make the request (table not found ?)",2);
            exit(1);
        }
        if ($nb_groupfilters = mysqli_num_rows($req)) {
            while ($groupfilter = mysqli_fetch_assoc($req)) {
                $group = $groupfilter['group_filter'];
                $this->log->LogDebug("ListTypes : filtre abonnes ".$groupfilter['group_filter']. " pour le modele $model_name");
            }
            mysqli_free_result($req);
        }
        mysqli_close($con);
        // On ne retourne que le premier car sympa n'en supporte qu'un
        return $group;
    }

    /**
     * Methode permettant de tester si la requete preparee dont l'identifiant est fourni
     * existe en base
     * @param <type> $id_request l'identifiant de la requete preparee
     * @return <type> booleen
     */
    public function isExistingPreparedRequest($id_request, $dbIndex) {
        $exists = false;
        $con = $this->connect_to_db($dbIndex);
        $sql = "SELECT count(id_request) AS value FROM prepared_request WHERE id_request=$id_request";
        if (!$req = mysqli_query($con, $sql)) {
            $this->log->LogError("ListTypes : Impossible d'effectuer la requete\n".$sql .mysqli_error());
            throw new ListTypesNoModelsFoundException("can't make the request (table not found ?)",2);
            exit(1);
        }
        $res = mysqli_fetch_assoc($req);
        if ($res['value'] == 1) {
            $exists = true;
            $this->log->LogDebug("ListTypes : la request preparee $id_request existe");
        }
        mysqli_free_result($req);
        mysqli_close($con);
        return $exists;
    }

}

?>
