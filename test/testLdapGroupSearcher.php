<?php

/**
 * Test de la classe LdapGroupSearcher
 *
 * @Purpose: Test de la classe LdapGroupSearcher
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

// Initialisation du Logger
$log = new KLogger ( "/tmp/testLdapGroupSearcher.log" , KLogger::DEBUG );
$GLOBALS['logger'] = $log;

// Test getter
$config = new Config();

// Recuperation d'une valeur de configuration avec le getter magique
$log->LogInfo("Debut du test LdapGroupSearcher");
//$log->LogInfo("Test Recuperation parametre qui fonctionne : ".$config->__get("param_rne_etab"));

$simple_search = "esco:admin:local:admin*_0410017W";

$complexe_search = "esco:admin:local:admin*_{RNE}";

// Test recherche simple
$log->LogInfo("Test Recherche Simple qui fonctionne");
try {
    $grouptest = GroupSearcher::search($simple_search);
    $log->LogInfo("Test OK : $grouptest");
}
catch(Exception $e) {
    $log->LogError("Test echoue : ".$e->getMessage());
    exit(1);
}

// Test recherche complexe OK
$log->LogInfo("Test Recherche Complexe qui fonctionne");
$array_values = array('RNE' => "0370001A", 'CLASSE' => "701");
try {
    $complexe_arg = ArgumentFiller::getEscapedFilledString($complexe_search, $array_values);
    $grouptest = GroupSearcher::search($complexe_arg);
    $log->LogInfo("Test OK : $grouptest");
}
catch(Exception $e) {
    $log->LogError("Test echoue : ".$e->getMessage());
    exit(1);
}

// Test recherche complexe qui echoue
$log->LogInfo("Test Recherche Complexe qui echoue : $grouptest");
$array_values = array('CLASSE' => "701");
try {
    $complexe_arg = ArgumentFiller::getEscapedFilledString($complexe_search, $array_values);
    $grouptest = GroupSearcher::search($complexe_arg);
    $log->LogInfo("Test echoue : la recherche n'aurait pas du reussir");
}
catch(ArgumentFillerException $e) {
    $log->LogInfo("Test OK : Message d'erreur obtenu : ".$e->getMessage());
    exit(1);
}
catch(Exception $e) {
    $log->LogError("Test echoue : Exception inconnue : ".$e->getMessage());
    exit(1);
}


$log->LogInfo("Fin du test LdapGroupSearcher");





/**
* Chargement automatique des classes (PHP 5.3.X)
* @param <type> $class_name
*/
function __autoload($class_name) {
    include "../lib/$class_name.class.php";
    //echo "../lib/$class_name loaded<br>";
}



?>
