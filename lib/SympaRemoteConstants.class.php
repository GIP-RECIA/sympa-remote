<?php

/**
 * @Class SympaRemoteConstants
 *
 * @Purpose: Classe contenant les valeurs des constantes
 *
 * @Author: Thomas BIZOUERNE, GIP RECIA 2011
 *
 */

class SympaRemoteConstants {

    // Nom des parametres d'entree attendus par sympa-remote
    const INPUT_OPERATION = "operation"; // L'operation demandee
    const INPUT_LIST_TYPE = "type"; // Le type de liste que l'on souhaite creer
    const INPUT_LIST_TYPE_PARAMETER = "type_param"; // un parametre facultatif qui peut etre fourni au type de liste
    const INPUT_RNE = "uai"; // Le RNE de l'etablissement concerne
    const INPUT_SIREN = "siren"; // Le SIREN de l'etablissement concerne
    const INPUT_WRITING_POLICY = "policy"; // La politique d'ecriture (qui a le droit)
    const INPUT_EDITORS_ALIASES = "editors_aliases"; // Des editeurs prédéfinis (connus en base) qui peuvent etre fournis. (Si aucun n'est fourni, sympa-remote prendra les editeurs marques comme obligatoires en base)
    const INPUT_EDITORS_GROUPS = "editors_groups"; // Des groupes supplementaires, facultatifs, qui peuvent etre fournis.


    // Valeurs possibles pour les parametres d'entree de sympa-remote

    // Pour l'operation demandee (creation seulement pour l'instant...)
    const OPERATION_CREATION_LISTE = "CREATE";

    // Pour la politique d'ecriture (WRITING POLICY)
    // Pour l'instant, on utilise qu'une seule politique d'écriture, toutes les personnes qui doivent pouvoir
    // ecrire a la liste seront ajoutees aux editeurs/moderateurs de la liste.
    const POLITIQUE_EDITEURS_SEULS = "newsletter"; // Seuls les editeurs peuvent ecrire

}

?>