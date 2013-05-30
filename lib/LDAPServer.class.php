<?php
 /**
  * Classe de connexion LDAP
  * Reprise de GEPI (gepi.mutualibre.org), simplifiee et adaptee
  */
class LDAPServer {
    
    /**** Logger ****/
    private $log = null;

    # Le chemin vers le fichier de configuration du LDAP.
	const config_file = "config/config.inc.php";

	# Les données pour se connecter à l'annuaire LDAP
	# Les champs login et password peuvent être laissés vides
	# dans le cas d'une connexion anonyme.
	private $host = "localhost";
	private $port = "389";
	private $login = "";
	private $password = "";
	public $base_dn = "o=gouv,o=fr";
	public $people_ou = "ou=people";
    public $groups_ou = "ou=groups";

	# Les classes de l'entrée LDAP d'un utilisateur. Elles doivent
	# être cohérentes avec les attributs utilisés.
	//private $people_object_classes = array("top","person","inetOrgPerson");

	private $champ_nom_groupe = "cn";

	# Cet attribut contient la connexion à l'annuaire LDAP. Cela
	# évite d'avoir à refaire plusieurs fois la connexion lors de
	# l'exécution d'un même script faisant appel à plusieurs reprises
	# à des requêtes vers l'annuaire.
	public $ds = false;


	public function __construct() {
		# On charge la configuration et on établit la connexion si
		# le serveur a été configuré.
        $this->log = $GLOBALS['logger'];
		if (self::is_setup()) {
			$this->load_config();
            $con = $this->connect();
            if ($con == false) {
                $this->log->LogError("LDAP Connection Error : check parameters");
                throw new Exception("LDAP CONNECTION ERROR : wrong parameters ?");
                exit(1);
            }
            $this->ds = $con;
		}
	}

	# Retourne un lien de connexion LDAP
	public function connect() {
		return self::connect_ldap($this->host, $this->port, $this->login, $this->password);
	}
	
	# Retourne le resultat de la recherche par le filtre suivant sur la branche fournie
	public function search($filtre, $branche) {
	        $sr = ldap_search($this->ds, $branche.",".$this->base_dn,$filtre);
	        $res = array();
	        $res = ldap_get_entries($this->ds, $sr);
	        if (!array_key_exists(0, $res)) {
	            $res = false;
	            //error_log("[LDAPServer (search)] Aucun resultat".$filtre);
	        }
	        return $res;
    	}


    # Permet de rechercher un groupe (par son cn) en donnant un filtre
    # Attention : ce filtre ne doit retourner qu'un seul et unique groupe.
	public function search_group($filtre) {
            $ldap_filter = $this->champ_nom_groupe."=".$filtre;
	        $sr = ldap_search($this->ds, $this->groups_ou.",".$this->base_dn,$ldap_filter,array("cn"),0,1);
	        $res = array();
	        $res = ldap_get_entries($this->ds, $sr);
	        if (!array_key_exists(0, $res)) {
	        	$groupname = $this->search_people($filtre);
                 $this->log->LogError("[LDAPServer (search)] Aucun groupe trouve (".$ldap_filter.")");
	        }
            else if (!array_key_exists($this->champ_nom_groupe,$res[0])) {
                $groupname = false;
                $this->log->LogError("[LDAPServer (search)] resultat anormal, pas de champ ".$this->champ_nom_groupe." retourne (".$ldap_filter.")");
            }
            else if (!array_key_exists(0,$res[0][$this->champ_nom_groupe])) {
                $groupname = false;
                $this->log->LogError("[LDAPServer (search)] resultat anormal, pas de valeur dans ".$this->champ_nom_groupe." (".$ldap_filter.")");
            }
            else {
                $groupname = $res[0][$this->champ_nom_groupe][0];
            }
	        return $groupname;
    }

    # Permet de rechercher un groupe (par son cn) en donnant un filtre
    # Attention : ce filtre ne doit retourner qu'un seul et unique groupe.
 	public function search_people($filtre) {
 	        $req = $filtre;
 			$sr = ldap_search($this->ds, $this->people_ou.",".$this->base_dn,$req,array("cn"),0,1);
 	        $res = array();
 	        $res = ldap_get_entries($this->ds, $sr);
 	        if (!array_key_exists(0, $res)) {
 	            	$groupname = false;
                 	$this->log->LogError("[LDAPServer (search)] Aucune personne trouve (".$filtre.")");
 	        }
            else {
                 	$groupname = $filtre;
            }	
 	        return $groupname;
    }



	/*
	* renvoie le dn de recherche dans le ldap
	*/
	private function get_dn(){
		return $this->people_ou.",".$this->base_dn;
	}


	public static function connect_ldap($_adresse,$_port,$_login,$_password) {
		# Pour avoir du débug en log serveur, décommenter la ligne suivante.
		#ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
	    $ds = ldap_connect($_adresse, $_port);
	    if($ds) {
	       // On dit qu'on utilise LDAP V3, sinon la V2 par d?faut est utilis? et le bind ne passe pas.
	       $norme = ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	       // Accès non anonyme
	       if ($_login != '') {
	          // On tente un bind
	          $b = ldap_bind($ds, $_login, $_password);
	       } else {
	          // Accès anonyme
	          $b = ldap_bind($ds);
	       }
	       if ($b) {
	           return $ds;
	       } else {
	           return false;
	       }
	    } else {
	       return false;
	    }
	}

	public static function is_setup() {
		return file_exists(dirname(__FILE__)."/../".self::config_file);
	}

	# On récupère les données de configuration présentes dans le fichier
	# /secure/config_ldap.inc.php
	private function load_config() {
		$ldap_config = array();
		if (self::is_setup()) {
			$path = dirname(__FILE__)."/../".self::config_file;
			include($path);

			$available_settings = get_class_vars(get_class($this));
			foreach($available_settings as $key => $value) {
				$varname = "ldap_".$key;
				if (isset($$varname)) {
					$this->$key = $$varname;
				}
			}
		}
	}

}
?>
