<?php

/**
 * @Class XMLBuilder
 *
 * @Purpose: Classe permettant de creer le fichier XML qui permettra l'instanciation de la liste
 * Il sera passe en ligne de commande a sympa.pl pour effectuer la creation de la liste dans
 * la bonne famille
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 * Classe permettant de creer le fichier XML qui permettra l'instanciation de la liste
 *
 */

class XMLBuilder {

    /* Balises du XML */
    const XML_TAG_LIST = "list";
    const XML_TAG_LISTNAME = "listname";
    const XML_TAG_SUBJECT = "subject";
    const XML_TAG_TOPICS = "topics";
    const XML_TAG_DESCRIPTION = "description";
    const XML_TAG_OWNERS_GROUP = "owners_group";
    const XML_TAG_EDITORS_FROM_REQUEST = "editors_from_request";
    const XML_TAG_EDITORS_FROM_GROUP = "editors_from_group";
    const XML_TAG_SUBSCRIBERS_GROUP = "subscribers_group";
    const XML_TAG_CREATION_EMAIL = "creation_email";
    const XML_TAG_SCENARIO_SEND = "scenario_send";

    // Sous-Balise particuliere
    const XML_TAG_GROUPNAME = "groupname";
    const XML_TAG_REQUEST = "request";
		//MADE pierre XML_TAG_SOURSE et suffix;
	const XML_TAG_SOURCE = "source";
	const XML_TAG_SUFFIX = "suffix";
		
    /**** Logger ****/
    static private $log = null;

    /**** Configuration ****/
    static private $config = null;

    /**** Contenu du fichier XML ****/
    static private $xml_content = null;

    /**
     * @Constructor Constructeur de la classe ListTypes
     */
    static public function init() {
        self::$log = $GLOBALS['logger'];
        self::$config = $GLOBALS['config'];
        // Verification des parametres internes necessaires au fonctionnement du ParamsWrapper
        self::$xml_content = "<?xml version=\"1.0\"?>\n";
    }

    /**
     * Fonction permettant de creer le XML d'instanciation d'une liste
     * a partir d'un tableau contenant tous les parametres attendus
     */
    static public function buildXML($values) {
        self::insertStartTag(self::XML_TAG_LIST,true);

        // Theoriquement, toutes les valeurs obligatoires sont presente, on ne controle pas une 2eme fois
        self::insertValueInTag(self::XML_TAG_LISTNAME, $values[self::XML_TAG_LISTNAME]);
        self::insertValueInTag(self::XML_TAG_SUBJECT, $values[self::XML_TAG_SUBJECT]);
        self::insertValueInTag(self::XML_TAG_TOPICS, $values[self::XML_TAG_TOPICS]);
        self::insertValueInTag(self::XML_TAG_DESCRIPTION, $values[self::XML_TAG_DESCRIPTION]);
        self::insertValueInTag(self::XML_TAG_OWNERS_GROUP, $values[self::XML_TAG_OWNERS_GROUP]);
        if (is_array($values[self::XML_TAG_EDITORS_FROM_REQUEST])) {
            foreach($values[self::XML_TAG_EDITORS_FROM_REQUEST] as $request) {
				if (is_Array($request) ) {
						//MADE pierre cas on o a dans l'ordre le filtre ldap, la data_source et le suffix ldap  
					self::insertValueInTag(self::XML_TAG_EDITORS_FROM_REQUEST, $request[0] , $request[1], $request[2]);	 
				} else {
					self::insertValueInTag(self::XML_TAG_EDITORS_FROM_REQUEST, $request);
				}
            }
        }
        if (is_array($values[self::XML_TAG_EDITORS_FROM_GROUP])) {
            foreach($values[self::XML_TAG_EDITORS_FROM_GROUP] as $groupname) {
                self::insertValueInTag(self::XML_TAG_EDITORS_FROM_GROUP, $groupname);
            }
        }
        self::insertValueInTag(self::XML_TAG_SUBSCRIBERS_GROUP, $values[self::XML_TAG_SUBSCRIBERS_GROUP]);
        //self::insertValueInTag(self::XML_TAG_SUBSCRIBERS_GROUP, $values[self::XML_TAG_SUBSCRIBERS_GROUP]);
        //self::insertValueInTag(self::XML_TAG_CREATION_EMAIL, $values[self::XML_TAG_CREATION_EMAIL]);
        self::insertValueInTag(self::XML_TAG_SCENARIO_SEND, $values[self::XML_TAG_SCENARIO_SEND]);


        self::insertEndTag(self::XML_TAG_LIST);
        return self::$xml_content;
    }

    /**
     * Fonction permettant d'ajouter une balise ouvrante
     * @param <type> $tagname la balise XML
     * @param <type> $cr passage a la ligne oui ou non (true/false)
     */
    static private function insertStartTag($tagname,$cr) {
        $multiple="";
        $n = "";
        if (strcmp($tagname, self::XML_TAG_EDITORS_FROM_GROUP) == 0 || strcmp($tagname, self::XML_TAG_EDITORS_FROM_REQUEST) == 0 || strcmp($tagname, self::XML_TAG_OWNERS_GROUP) == 0 ) {
            $multiple = " multiple=\"1\"";
        }
        if ($cr) { $n = "\n"; }
        self::$xml_content=self::$xml_content."<$tagname$multiple>$n";
    }

    /**
     * Fonction permettant d'ajouter une balise fermante
     * @param <type> $tagname la balise XML
     */
    static private function insertEndTag($tagname) {
        self::$xml_content=self::$xml_content."</$tagname>\n";
    }

    /**
     * Fonction permettant d'ajouter une valeur au xml
     * @param <type> $value la valeur a inserer
     */
    static private function insertValue($value) {
        self::$xml_content=self::$xml_content.$value;
    }

    /**
     * Fonction permattant d'ajouter une valeur entre une balise ouvrante et fermante
     * @param <type> $tagname la balise
     * @param <type> $value la valeur a inserer
     * @param <type> $source falcultative a inserer pour le tag EDITORS_FROM_REQUEST
     * @param <type> $suffix falcultatif a inserer pour le tag EDITORS_FROM_REQUEST
     */
    static private function insertValueInTag($tagname, $value, $source=null, $suffix=null) {
        if (strcmp($tagname, self::XML_TAG_EDITORS_FROM_GROUP) == 0 || strcmp($tagname, self::XML_TAG_OWNERS_GROUP) == 0) {
            self::insertStartTag($tagname,true);
            self::insertValueInTag(self::XML_TAG_GROUPNAME, $value);
            self::insertEndTag($tagname);
        }
        else if (strcmp($tagname, self::XML_TAG_EDITORS_FROM_REQUEST) == 0) {
            self::insertStartTag($tagname,true);
            self::insertValueInTag(self::XML_TAG_REQUEST, $value);
            if ($source != null) {//MADE pierre
				self::insertValueInTag(self::XML_TAG_SOURCE, $source);
			}
			if ($suffix != null) {//MADE pierre
				self::insertValueInTag(self::XML_TAG_SUFFIX, $suffix);
			}
            self::insertEndTag($tagname);
        }  
        else {
            self::insertStartTag($tagname,false);
            self::insertValue($value);
            self::insertEndTag($tagname);
        }
    }
}
XMLBuilder::init();
?>
