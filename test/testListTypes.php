<?php

/**
 * Test de la classe ListTypes qui charge les types et recupere les parametres
 * de chaque type dans la base de donnees
 *
 * @Purpose: Test de la classe SympaPLClient
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

// Initialisation du Logger
$log = new KLogger ( "/tmp/testListTypes.log" , KLogger::DEBUG );
$GLOBALS['logger'] = $log;

// Test getter
$config = new Config();

// Recuperation d'une valeur de configuration avec le getter magique
$log->LogInfo("Debut du test ListTypes");
$log->LogInfo("Test Recuperation parametre qui fonctionne : ".$config->__get("db_host"));

try {
    $types = new ListTypes();
    $types->getModeleProperties('tous_personnels', ListTypes::NOM_LISTE, "Default");
    $types->getModeleProperties('tous_personnels', ListTypes::DESC, "esco");
    $types->getModeleProperties('tous_personnels', ListTypes::CATEGORIE, "esco");
    $types->getModeleProperties('tous_personnels', ListTypes::SUJET, "esco");
    $types->getModeleProperties('tous_personnels', ListTypes::FAMILLE, "cfa");
    $types->getModeleProperties('tous_personnels', ListTypes::EDITEURS_OBLIGATOIRES, "cfa");
    $types->getModeleProperties('tous_personnels', ListTypes::EDITEURS_COCHES, "cfa");
    $types->getModeleProperties('tous_personnels', ListTypes::EDITEURS_NON_COCHES, "Default"));
    $types->getModeleProperties('tous_personnels', ListTypes::ABONNES, "Default"));
}
catch(Exception $e) {
    $log->LogError("Test echoue : Exception inconnue : ".$e);
    exit(1);
}

$log->LogInfo("Fin du test ListTypes");





/**
* Chargement automatique des classes (PHP 5.3.X)
* @param <type> $class_name
*/
function __autoload($class_name) {
    include "../lib/$class_name.class.php";
    //echo "../lib/$class_name loaded<br>";
}



?>
