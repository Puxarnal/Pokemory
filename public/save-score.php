<?php
// Toujours commencer par importer le minimum syndical
require_once __DIR__ . '/../src/boot.php';

// import des classes utilisées dans le script
use Pokemory\Manager\ScoreManager;
use Pokemory\Model\Score;
use Pokemory\Utils;

/**
 * Commençons par récupérer la donnée envoyée via
 * la requête HTTP...
 * 
 * Si aucune valeur n'est définie dans les données
 * postées, on considérera qu'elle a été transmise avec
 * la valeur `null`. Ceci nous permettra de traiter
 * ce cas comme une erreur de validation.
 * 
 * @see https://www.php.net/manual/fr/migration70.new-features.php#migration70.new-features.null-coalesce-op
 */
$time = $_POST['time'] ?? null;

/**
 * It's validation time !
 * (C'est fastidieux mais c'est NÉCESSAIRE)
 *
 * On estime que l'entrée `time` est valide si...
 */
$time_is_valid = (
    /**
     * ...si c'est une valeur "numérique" (c'est-à-dire
     * que PHP estime pouvoir caster en nombre)
     * 
     * Remarque :
     * Les formats les plus courants pour transmettre
     * des données via une requête HTTP POST n'ont
     * aucun typage. Par conséquent, lorsque PHP
     * parse le corps de ces requêtes, il extrait les
     * données sous la forme de chaînes de caractères
     * et laisse au développeur la responsabilité
     * d'effectuer les vérifications et les
     * manipulations nécessaires à leur interprétation.
     * Ici, nous devons donc recevoir la durée d'une partie
     * de jeu sous la forme d'une chaîne de caractères
     * représentant un nombre.
     */
    is_numeric($time)
    /**
     * ...et si la valeur numérique représentée est
     * supérieure à zéro
     * 
     * Remarque :
     * Ici, je suis un peu laxiste car les valeurs
     * comprises entre 0 et 1 seront valides ; mais
     * je me le permets car je sais que de toute façon
     * j'enregistrerai un arrondi à l'entier supérieur
     * (donc 1 dans ce cas-là). Bien évidemment, on
     * pourrait choisir de tronquer les valeurs
     * décimales ou de considérer que seules les valeurs
     * entières sont valides... It's up to you !
     */
    && floatval($time) > 0
);

// Si la donnée fournie est invalide...
if (!$time_is_valid) {
    /**
     * ...on renvoie un code de réponse HTTP approprié...
     * 
     * J'opte ici pour un statut 422 (Unprocessable Entity),
     * qui indique au client que le serveur a bien compris
     * la requête mais qu'il ne peut la traiter à cause
     * d'erreurs qui ne relèvent pas de sa syntaxe ni de
     * sa structure. Un autre statut HTTP couramment
     * utilisé pour signifier une erreur de validation
     * est 400 (Bad Request).
     * 
     * @see https://developer.mozilla.org/fr/docs/Web/HTTP/Status/422
     * @see https://developer.mozilla.org/fr/docs/Web/HTTP/Status/400
     */
    http_response_code(422);

    /**
     * ...et on donne également quelques détails au client
     * (même si celles et ceux qui suivent auront remarqué
     * que ce n'est pas vraiment exploité par ce dernier...)
     * 
     * Note:
     * On génère ici une réponse au format JSON car celle-ci
     * est destinée à être manipulée via JavaScript et il
     * se trouve que ce format est facilement manipulable
     * par ce langage (JSON = JavaScript Objet Notation...)
     */
    header('Content-Type: application/json');
    echo json_encode(['valid' => false]);

    /**
     * Et puisqu'on n'aura pas plus de traitement en cas
     * de données invalide : petit early exit en PHP en
     * dehors d'une fonction !
     * Si, si, ça fonctionne... On pourrait aussi utiliser
     * `exit()` ou `die()` :-)
     */
    return;
}

/**
 * La donnée est, il est maintenant temps de
 * créer notre objet :
 */
$score = new Score();

/**
 * La classe Score s'attend à ce qu'on lui donne le temps
 * d'une partie sous la forme d'un objet `DateInterval`.
 * Or nous ne disposons que d'une chaîne qui représente
 * une durée en secondes.
 * Alors il va falloir faire quelques convesions...
 * 
 * @see https://www.php.net/manual/fr/class.dateinterval.php
 */

/**
 * Conversion: string => float
 * Je vous avais bien dit que j'allais arrondir, non ? ;-)
 */
$seconds = round(floatval($time));

/**
 * Conversion: float => DateInterval
 * Malheureusement PHP ne fournit pas de moyen natif de
 * convertir facilement un nombre en `DateInterval`.
 * C'est dans ces grands moments de solitude qu'il faut
 * faire un peu d'algo et se créer ses propres outils...
 * 
 * (...ou utiliser ceux des autres !)
 */
$interval = Utils::secondsToInterval($seconds);

// On renseigne enfin ce #@$^%!& temps de partie !
$score->setTime($interval);

// On tente d'enregistrer le score en base de données...
if (!ScoreManager::save($score)) {
    /**
     * ...mais si l'enregistrement se passe mal, on
     * renvoie encore une fois un code HTTP approprié !
     * Ici, j'opte pour le statut 500 (Interval Server Error)
     * pour indiquer au client que le problème vient bien du
     * serveur.
     * 
     * Causes les plus probables de l'échec de l'enregistrement :
     *      - une erreur PHP dans la méthode du manager
     *      - une erreur SQL
     *      - la base de données est morte
     *      - les Internets sont morts
     *      - un datacenter a pris feu
     *      - le monde n'est plus (mais à ce stade-là...)
     *
     * (Sans blague, la plupart de ces choses arrivent pour de vrai.)
     */
    http_response_code(500);
}

/**
 * Si vous êtes ici, c'est que tout s'est bien passé.
 * Le serveur va envoyer sa réponse au client avec un statut 200 (OK)
 * pour indiquer que la requête a été traitée avec succès.
 */
