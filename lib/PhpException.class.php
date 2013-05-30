<?php

/**
 *
 * @Class PhpException
 *
 * @Purpose: Exception parente de toutes les autres exceptions
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 * Exception parente de toutes les autres exceptions
 * Afin de pouvoir garder l'enchainement des Exceptions (avec previous)
 *
 * Cette fonctionnalite n'a ete prevue qu'a partir de PHP 5.3 directement
 * dans la classe Exception.
 *
 */

class PhpException extends Exception
{
    private $previous;

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code);

        if (!is_null($previous))
        {
            $this -> previous = $previous;
        }
    }

    public function getPrev()
    {
        return $this -> previous;
    }
}

?>
