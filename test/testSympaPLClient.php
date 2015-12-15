<?php

/**
 * Test de la classe SympaPLClients
 *
 * @Purpose: Test de la classe SympaPLClients
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

// Initialisation du Logger
$log = new KLogger ( "/tmp/testSympaPLClient.log" , KLogger::DEBUG );
$GLOBALS['logger'] = $log;

// Test getter
$config = new Config();

// Recuperation d'une valeur de configuration avec le getter magique
$log->LogInfo("Debut du test SympaPLClient");
$log->LogInfo("Test Recuperation parametre qui fonctionne : ".$config->__get("db_db"));


// tableau de parametres
$input_params = array();

// Test Close / create list
doCloseTestWith("Close list 'eleves de la classe 701'", "eleves701", true);
doCreateTestWith("Create list 'eleves de la classe 701'", "eleves_classe","CLASSE$701", "0410017W", "newsletter", "", true);


function doCloseTestWith($test_desc,$listNameToClose,$must_succeed) {
    echo "TEST : $test_desc : ";
    $GLOBALS['logger']->LogError("Test execute : $test_desc ");
    $succeed = true;
    $input_params[SympaRemoteConstants::INPUT_OPERATION] = "CLOSE";
    $input_params[SympaRemoteConstants::INPUT_LIST_NAME_TO_CLOSE]=$listNameToClose . "@0450822x.list.netocentre.fr";
    try {
        $params_wrapper = new ParamsWrapper($input_params);
        $params_wrapper->check();
        $params_wrapper->wrap();
        $xml_content = XMLBuilder::buildXML($params_wrapper->getWrappedParameters());
        echo "<xmp>";
        print_r($xml_content);
        echo "</xmp>";
        $sympa_client = new SympaPLClient();

        $sympa_client->closeList($listNameToClose);
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
        echo "<font color=\"red\">FAILED (Maybe the list wasnt created before. You should rerun the test.)</font>";
        $GLOBALS['logger']->LogInfo("Test ECHOUE");
    }
    echo "<br>";
}

function doCreateTestWith($test_desc,$operation,$type,$type_param,$rne,$policy,$editors_aliases,$must_succeed) {
    echo "TEST : $test_desc : ";
    $GLOBALS['logger']->LogError("Test execute : $test_desc ");
    $succeed = true;
    $input_params[SympaRemoteConstants::INPUT_OPERATION] = "CREATE";
    $input_params[SympaRemoteConstants::INPUT_LIST_TYPE]=$type;
    $input_params[SympaRemoteConstants::INPUT_LIST_TYPE_PARAMETER]=$type_param;
    $input_params[SympaRemoteConstants::INPUT_RNE]=$rne;
    $input_params[SympaRemoteConstants::INPUT_WRITING_POLICY]=$policy;
    $input_params[SympaRemoteConstants::INPUT_EDITORS_ALIASES]=$editors_aliases;

    try {
        $params_wrapper = new ParamsWrapper($input_params);
        $params_wrapper->check();
        $params_wrapper->wrap();
        $xml_content = XMLBuilder::buildXML($params_wrapper->getWrappedParameters());
        echo "<xmp>";
        print_r($xml_content);
        echo "</xmp>";
        $sympa_client = new SympaPLClient();

        $sympa_client->createListWithXML($xml_content, $params_wrapper->getWrappedParameter(SympaPLClient::ARGUMENT_FAMILLE), "0450822x.list.netocentre.fr");
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


$log->LogInfo("Fin du test SympaPLClient");





/**
* Chargement automatique des classes (PHP 5.3.X)
* @param <type> $class_name
*/
function __autoload($class_name) {
    include "../lib/$class_name.class.php";
    //echo "../lib/$class_name loaded<br>";
}



?>

