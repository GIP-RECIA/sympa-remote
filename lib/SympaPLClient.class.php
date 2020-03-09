<?php

/**
 * @Class SympaPLClient
 *
 * @Purpose: Classe permettant de gerer l'appel de la commande sympa.pl avec les parametres
 * adequats.
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 * Classe permettant de gerer l'appel de la commande sympa.pl avec les parametres
 * adequats.
 *
 */

class SympaPLClient {

    /* Balises du XML */
    // Argument permettant de specifier la famille dans laquelle ajouter la liste
    const ARGUMENT_FAMILLE = "--add_list";
    const ARGUMENT_ADD_COMMAND = "--add_list";
    const ARGUMENT_MODIFY_COMMAND = "--modify_list";
    const ARGUMENT_CLOSE_COMMAND = "--close_list";

    const ARGUMENT_ROBOT = "--robot";
    const ARGUMENT_FICHIER_XML = "--input_file";

    const SYMPA_BIN = "sympa.pl";

    const CODE_OK = 0;
    const CODE_ALLREADY_EXISTS = 1;
    const CODE_FAILED = 2;

    /**** Logger ****/
    private $log = null;

    /**** Configuration ****/
    private $config = null;

    /**** pointeur vers le fichiers XML temporaire ecrit ****/
    private $filename = null;

    /**
     * @Constructor Constructeur de la classe ListTypes
     */
    public function __construct() {
        $this->log = $GLOBALS['logger'];
        $this->config = $GLOBALS['config'];

        // Verification des parametres internes necessaires au fonctionnement du ParamsWrapper
        $this->check_required_parameters();

    }

    /**
     * Fonction permettant de verifier que les parametres necessaires au fonctionnement de ce module
     * sont bien presents et recuperables dans la configuration.
     */
    private function check_required_parameters() {
        $this->log->LogDebug("SympaPLClient : Verification de la config necessaire");
        //ConfigChecker::raise_exception_if_param_not_defined("POLITIQUE_STRICTE");
        $this->log->LogDebug("SympaPLClient : Verification de la config necessaire OK");
    }

    /**
     * Fonction permettant de creer la liste avec sympa.pl a partir du flux xml permettant
     * l'instanciation de la liste
     * @param <type> $xml_content le flux XML d'instanciation de la liste
     * @param <type> $family la famille dans laquelle creer la liste
     * @param <type> $robot le robot sympa sur lequel creer la liste
     */
    public function createListWithXML($xml_content, $family, $robot) {
        $chemin_xml = $this->createTemporaryXMLFile($xml_content);
        $this->executeSympaCommand($family, $robot, self::ARGUMENT_ADD_COMMAND, false);
        //unlink($chemin_xml);
    }

    /**
     * Fonction permettant de creer la liste avec sympa.pl a partir du flux xml permettant
     * l'instanciation de la liste
     * @param <type> $xml_content le flux XML d'instanciation de la liste
     * @param <type> $family la famille dans laquelle creer la liste
     * @param <type> $robot le robot sympa sur lequel creer la liste
     */
    public function updateListWithXML($xml_content, $family, $robot) {
        $chemin_xml = $this->createTemporaryXMLFile($xml_content);
        $this->executeSympaCommand($family, $robot, self::ARGUMENT_MODIFY_COMMAND, true);
        //unlink($chemin_xml);
    }

    /**
     * Renomme une  une liste.
     * @param <type> $listname le nom de la liste
     */
    private function renameList($listname, $robot) {
		$CMD_PATH = $this->config->sympa_bin_dir;
		$CMD = self::SYMPA_BIN;
		if (strlen ($listname) > 30 ) {
	       $date= date('.Y.m.d');
	       $newName = substr($listname, 0, 39) . $date;
        } else {
	       $date= date('.Y.m.d.H.i.s');
	       $newName = $listname . $date;
        }
		$date = date('.Y.m.d.H.i.s');
        $CMD_ARGS = "--rename_list $listname@$robot --new_listname=$newName --new_listrobot=$robot";
        $this->log->LogDebug("SympaPLClient : execution de la commande de renommage $CMD_ARGS");
        $start = time();
        exec("$CMD_PATH$CMD $CMD_ARGS 2>&1", $output, $returnVal);
        $end = time();
        $duration = $end-$start;
        $this->log->LogDebug("SympaPLClient : duree = $duration secondes return $returnVal");
        return !$returnVal;
    }
    /**
     * Ferme une liste.
     * @param <type> $listname le nom de la liste
     */
    public function closeList($listname) {
		$CMD_PATH = $this->config->sympa_bin_dir;
        $CMD = self::SYMPA_BIN;
        $CMD_ARGS = self::ARGUMENT_CLOSE_COMMAND . " $listname";
        //echo "$CMD_PATH$CMD $CMD_ARGS 2>&1";
        $this->log->LogDebug("SympaPLClient : execution de la commande de cloture $CMD_ARGS");
        $start = time();
        //ob_start();
        //echo "$CMD_PATH$CMD $CMD_ARGS 2>&1";
        exec("$CMD_PATH$CMD $CMD_ARGS 2>&1", $output, $returnVal);
        $end = time();
        $duration = $end-$start;
        $this->log->LogDebug("SympaPLClient : duree = $duration secondes, $returnVal");
        $this->analyzeCloseOutput($output, $returnVal);
	if (!$returnVal) {
		list($listname, $robot) =  explode("@", $listname);
		$this->renameList($listname,$robot);
	}
    }

    /**
     * Fonction permettant de creer le XML temporaire a partir de son contenu
     * @param <type> $xml_content Flux XML a inserer dans le fichier
     */
    private function createTemporaryXMLFile($xml_content) {
        //$filename = UniqueFilenameBuilder::getUniqueName().".xml";
        $this->filename = tempnam("/tmp", "sympaRemote_");
        $file = fopen($this->filename, "w");
        fwrite($file, utf8_encode($xml_content));
        fclose($file);
        $this->log->LogDebug("SympaPLClient : creation du fichier xml temporaire $this->filename OK");
    }

    /**
     * Fonction permettant de lancer l'execution d'une commande sympa.
     * (avec 'sympa.pl')
     * @param <type> $family
     * @param <type> $robot
     * @param <type> $command the sympa command
     * @param <type> $allowUpdate true if an update is authorized
     */
    private function executeSympaCommand($family, $robot, $command, $allowUpdate) {
        $CMD_PATH = $this->config->sympa_bin_dir;
        $CMD = self::SYMPA_BIN;
        $CMD_ARGS = "$command $family ".self::ARGUMENT_ROBOT." $robot ".self::ARGUMENT_FICHIER_XML." ".$this->filename;
        //echo "$CMD_PATH$CMD $CMD_ARGS 2>&1";
        $this->log->LogDebug("SympaPLClient : execution de la commande $CMD_PATH$CMD  $CMD_ARGS");
        $start = time();
        //ob_start();
        //echo "$CMD_PATH$CMD $CMD_ARGS 2>&1";
        exec("$CMD_PATH$CMD $CMD_ARGS 2>&1", $output, $returnVal);
        $end = time();
        $duration = $end-$start;
        $this->log->LogDebug("SympaPLClient : duree = $duration secondes; returnVal = $returnVal");
        $this->analyzeOutput($output, $allowUpdate, $returnVal);
    }

    /**
     * Fonction de test...
     * Execution du script directement via un interpreter Perl, grace a php/perl
     * Pb : le code de sympa.pl est appele via l'instruction require()
     * et cette instruction, meme directement en perl, fait que tout code qui vient
     * apres n'est pas execute.
     *
     * A TESTER : eval(do ScriptName.pl) => execute le fichier, different de require.
     * @param <type> $family
     * @param <type> $robot
     */
    private function executePerlCreateList($family, $robot) {
        $CMD_PATH = $this->config->sympa_bin_dir;
        $CMD = self::SYMPA_BIN;
        $CMD_ARGS = self::ARGUMENT_ADD_COMMAND." $family ".self::ARGUMENT_FICHIER_XML." ".$this->filename;
        //echo "$CMD_PATH$CMD $CMD_ARGS 2>&1";
        $this->log->LogDebug("SympaPLClient : execution de la commande de creation");
        $start = time();
		//exec("$CMD_PATH$CMD $CMD_ARGS 2>&1", $output);
        $perl = new Perl();
        $perl->eval("@ARGV=('--add_list','tous_les_personnels_etab','--input_file', '/tmp/sympaRemote_WzhSZb')");
        $this->log->LogDebug("SympaPLClient : eval OK");
        ob_start();
        $this->log->LogDebug("SympaPLClient : ob_start OK");
        $perl->require("$CMD_PATH$CMD");
        $this->log->LogDebug("SympaPLClient : commande OK");
        $output = ob_get_contents();
        $this->log->LogDebug("SympaPLClient : ob_get_contents OK");
        ob_end_clean();
        $this->log->LogDebug("SympaPLClient : ob_clean OK");
        $end = time();
        $duration = $end-$start;
        $this->log->LogDebug("SympaPLClient : duree = $duration secondes");
        //print_r($output);
    }

    /**
     * Fonction permettant de deduire la reussite ou non de la commande de creation de liste
     * en fonction de la sortie recuperee
     * @param <type> $output la sortie de la commande
     * @param <type> $allowUpdate if true analyze the output of a sympa update
     */
    private function analyzeOutput($output, $allowUpdate, $returnVal) {
        $return = self::CODE_OK;
	if (!$returnVal) return $return;
        if ( !is_array($output) ) {
            $this->log->LogWarn("SympaPLClient : une creation de liste a echouee (output non tableau)");
            throw new SympaPLClientListCreationFailedException("ERROR_CREATING_LIST",1);
            exit(1);
        }
	
		$out = implode("", $output);

		if ($allowUpdate) {
			if (!(strstr($out, "list has been modified.") != false)) {
				//The list was not modified

				$this->log->LogWarn("SympaPLClient : une modification de liste a échouée ! \n");
				foreach ($output as $line) {
			    	$this->log->LogWarn("SympaPLClient : $line\n");
				}
			
				throw new PhpException("ERROR_UPDATING_LIST",1);
	        	exit(1);
			}
		} else {
			if (!(strstr($out, "err admin::install_aliases() admin::install_aliases : Aliases installed successfully") != false)) {
		    	// La liste n'existait pas, aucune erreur, elle a ete cree

		    	if (strstr($out, "some alias already exist") != false) {
					// La liste existait deja... sympa l'a mise a jour, les alias existaient deja.
					$this->log->LogWarn("SympaPLClient : une creation de liste a reussie, mais les alias existaient deja (details a suivre)");
					foreach ($output as $line) {
			    		$this->log->LogWarn("SympaPLClient : $line\n");
					}
					throw new SympaPLClientListAlreadyExistsException("LIST_ALREADY_EXISTS",1);
					exit(1);
		    	} else {
					$this->log->LogError("SympaPLClient : une creation de liste a echouee (details a suivre)\n");
					foreach ($output as $line) {
			    		$this->log->LogError("SympaPLClient : $line\n");
					}
					throw new SympaPLClientListCreationFailedException("ERROR_CREATING_LIST",1);
					exit(1);
		    	}
	    	}
		}
	}

    /**
     * Fonction permettant de deduire la reussite ou non de la commande de fermeture de liste
     * en fonction de la sortie recuperee
     * @param <type> $output la sortie de la commande
     */
    private function analyzeCloseOutput($output, $returnVall) {
	if (!$returnVal) return ;
        if (!is_array($output)) {
            $this->log->LogWarn("SympaPLClient : une fermeture de liste a echouee (output non tableau)");
            throw new PhpException("ERROR_CLOSING_LIST",1);
            exit(1);
        }

		$out = implode("", $output);
		if (!(strstr($out, "has been closed, aliases have been removed") != false)) {
			//The list was not modified

			$this->log->LogWarn("SympaPLClient : une fermeture de liste a échouée ! \n");
			foreach ($output as $line) {
		    	$this->log->LogWarn("SympaPLClient : $line\n");
			}
		
			throw new PhpException("ERROR_CLOSING_LIST",1);
			exit(1);
		}
		$this->log->LogDebug("SympaPLClient : ---------- Retour de sympa.pl ---------- \n");
		foreach ($output as $line) {
	    	$this->log->LogDebug("SympaPLClient : $line\n");
		}
		$this->log->LogDebug("SympaPLClient : ---------- Retour de sympa.pl ---------- \n");
    }
}

?>
