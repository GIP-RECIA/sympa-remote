<?php

/**
 * Test de la classe ParamsWrappers
 *
 * @Purpose: Test de la classe ParamsWrappers
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

// Initialisation du Logger
$log = new KLogger ( "/tmp/testParamsWrapper.log" , KLogger::DEBUG );
$GLOBALS['logger'] = $log;

// Test getter
$config = new Config();

// Recuperation d'une valeur de configuration avec le getter magique
$log->LogInfo("Debut du test ParamsWrapper");
$log->LogInfo("Test Recuperation parametre qui fonctionne : ".$config->__get("db_db"));


// tableau de parametres
$input_params = array();


// Test pour des parametres manquants
doTestWith("parametre manquant (operation)","","tous_personnels","", "0410017W", "STRICT", "1", false);
doTestWith("parametre manquant (type de liste)","CREATE", "","", "0410017W", "STRICT", "1",false);
doTestWith("parametre manquant (parametre de type)","CREATE", "eleves_classe","", "0410017W", "STRICT", "1",false);
doTestWith("parametre manquant (rne)","CREATE", "tous_personnels","", "", "STRICT", "1",false);
doTestWith("parametre manquant (politique ecriture)","CREATE", "tous_personnels","", "0410017W", "", "1",false);
// Le parametre groupes n'est pas obligatoire
doTestWith("parametre facultatif manquant (alias d'editeurs)","CREATE", "tous_personnels","", "0410017W", "STRICT", "",true);

// Test de mauvaises valeurs pour les parametres
doTestWith("mauvaise operation","BAD", "tous_personnels","CLASSE$701", "0410017W", "EXTENDED", "1",false);
doTestWith("mauvais type de liste","CREATE", "bad_type","CLASSE$701", "0410017W", "EXTENDED", "1",false);
doTestWith("parametre de type requis mais non present","CREATE", "eleves_classe","", "0410017W", "EXTENDED", "1",false);
doTestWith("rne non present","CREATE", "tous_personnels","CLASSE$701", "", "EXTENDED", "1",false);
doTestWith("mauvaise politique d'ecriture","CREATE", "tous_personnels","CLASSE$701", "0410017W", "BAD_POL", "1",false);
doTestWith("alias d'editeur inexistant","CREATE", "tous_personnels","CLASSE$701", "0410017W", "BAD_POL", "180",false);
doTestWith("un alias d'editeur inexistant parmi plusieurs","CREATE", "tous_personnels","CLASSE$701", "0410017W", "BAD_POL", "1$180",false);



function doTestWith($test_desc,$operation,$type,$type_param,$rne,$policy,$editors_aliases,$must_succeed) {
    echo "TEST : $test_desc : ";
    $GLOBALS['logger']->LogError("Test execute : $test_desc ");
    $succeed = true;
    $input_params[SympaRemoteConstants::INPUT_OPERATION]=$operation;
    $input_params[SympaRemoteConstants::INPUT_LIST_TYPE]=$type;
    $input_params[SympaRemoteConstants::INPUT_LIST_TYPE_PARAMETER]=$type_param;
    $input_params[SympaRemoteConstants::INPUT_RNE]=$rne;
    $input_params[SympaRemoteConstants::INPUT_WRITING_POLICY]=$policy;
    $input_params[SympaRemoteConstants::INPUT_EDITORS_ALIASES]=$editors_aliases;
    try {
        $params_wrapper = new ParamsWrapper();
        $params_wrapper->check();
        $params_wrapper->wrap();
    }
    catch(ParamsWrapperCheckException $e) {
        $GLOBALS['logger']->LogError("Erreur lors de la verification des parametres : ".$e);
        $succeed = false;
    }
    catch(ParamsWrapperWrapException $e) {
        $GLOBALS['logger']->LogError("Erreur lors de la transformation des parametres : ".$e);
        $succeed = false;
    }
    catch(Exception $e) {
        $GLOBALS['logger']->LogError("Exception inconnue : ".$e);
        $succeed = false;
    }
    if  ($must_succeed == $succeed) {
        echo "<font color=\"green\">OK</font>";
        $GLOBALS['logger']->LogInfo("Test OK");
    }
    else {
        echo "<font color=\"red\">FAILED</font>";
        $GLOBALS['logger']->LogInfo("Test ECHOUE");
    }
    echo "<br>";
}


$log->LogInfo("Fin du test ParamsWrapper");





/**
* Chargement automatique des classes (PHP 5.3.X)
* @param <type> $class_name
*/
function __autoload($class_name) {
    include "../lib/$class_name.class.php";
    //echo "../lib/$class_name loaded<br>";
}



?>
