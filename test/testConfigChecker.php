<?php

/**
 * Test de la classe ConfigChecker
 *
 * @Purpose: Test de la classe ConfigChecker
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

// Initialisation du Logger
$log = new KLogger ( "/tmp/testConfigChecker.log" , KLogger::DEBUG );
$GLOBALS['logger'] = $log;

// Test getter
$config = new Config();

// Recuperation d'une valeur de configuration avec le getter magique
$log->LogInfo("Debut du test ConfigChecker");

// TEST DE LA CONFIG SYMPA-REMOTE
try {
    ConfigChecker::check();
    $log->LogInfo("Test OK");
}
catch(SympaRemoteBadConfigurationException $e) {
    $log->LogInfo("ERREUR : Le test a echoue");
}

$log->LogInfo("Fin du test ConfigChecker");





/**
* Chargement automatique des classes (PHP 5.3.X)
* @param <type> $class_name
*/
function __autoload($class_name) {
    include "../lib/$class_name.class.php";
    //echo "../lib/$class_name loaded<br>";
}



?>
