<?php
/**
 * sympa-remote
 * @author Recia - Thomas BIZOUERNE
 *
 * Sympa-Remote permet d'executer des commandes Sympa (via sympa/pl)
 * sur la machine locale.
 * Le but est de pouvoir creer des listes (dans une famille), e partir
 * de modeles. (ce qui n'est pas supporte par le webservice sympa)
 *
 * Les parametres sont passes au script PHP par methode POST.
 *
 */

function myautoload($class_name) {
    //echo $class_name."<br>";
    if (file_exists("lib/$class_name.class.php")) {
        require_once("lib/$class_name.class.php");
    }
}
spl_autoload_register("myautoload");

try {
    $service = new SympaRemoteCore();
    $service->do_service();
    echo "0,OK";
}
catch (SympaPLClientListAlreadyExistsException $e) {
    echo "0,".$e->getMessage();
    exit(1);
}
catch (Exception $e) {
    echo "1,".$e->getMessage();
    exit(1);
}

?>
