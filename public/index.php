<?php
// Toujours commencer par importer le minimum syndical
require_once __DIR__ . '/../src/boot.php';

// import des classes utilisées dans le script
use Pokemory\Manager\ScoreManager;
use Pokemory\Manager\PokemonManager;

?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>
        Pokémory
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="/img/favicon.png" />
    <link rel="stylesheet" type="text/css" href="/build/css/main.css" />
</head>

<body>
    <div class="container">
        <header class="mt-4 mb-3">
            <nav class="text-center">
                <a href="/" title="Revenir à l'accueil">
                    <img class="mw-100 h-auto" src="/img/logo.png" alt="Pokémory - Gotta find 'em all" />
                </a>
            </nav>
        </header>
        <main>
            <!-- Zone dans laquelle va se dérouler le jeu -->
            <div class="game" aria-live="polite">

                <!-- Écran de début du jeu -->
                <div class="game-start">
                    <div class="mt-5">

                        <h2>
                            Meilleurs temps
                        </h2>
                        <?php
                        /**
                         * On récupère les 10 meilleurs scores via une classe
                         * dont la seule raison d'être est de manipuler des
                         * scores (d'où le terme "manager").
                         * Le fait d'isoler tout le processus de récupération
                         * dans une fonction (ici une méthode statique)
                         * permet de découper la logique d'une application en
                         * "briques" plus maintenables et qui ont chacune un
                         * objectif précis.
                         * 
                         * Ici, la méthode appelée va aller chercher en base
                         * de données les 10 temps de jeu les plus courtes
                         * et les retourner sous la forme d'une liste d'objets
                         * de la classe Pokemory\Model\Score, triés par
                         * temps de jeu croissant.
                         */
                        $best_scores = ScoreManager::findBestScores(10);

                        /**
                         * Il est peut-être moins frustrant pour un utilisateur
                         * de constater un manque de donnée qu'un vide intersidéral,
                         * alors gérons le cas où notre manager ne nous renverrait
                         * aucun objet.
                         * 
                         * Bonus : dans ce projet, les managers retournent un
                         * tableau vide lorsque la base de donnée ne renvoie
                         * aucun résultat, mais aussi lorsqu'une erreur se produit
                         * dans l'exécution de la requête SQL ; dans ce dernier
                         * cas, on ment un peu à l'utilisateur, mais c'est
                         * toujours moins pire que de lui afficher le message
                         * d'erreur SQL, non ? ;-)
                         */
                        if (count($best_scores) == 0) {
                        ?>
                            <p>
                                Il semblerait que personne n'a encore joué à Pokémory.
                            </p>
                        <?php
                        } else {
                        ?>
                            <ol>
                                <?php
                                /**
                                 * Affichage des meilleurs scores
                                 */
                                foreach ($best_scores as $score) {

                                    $time = $score->getTime();

                                ?>
                                    <li>
                                        <!--
                                            L'utilisation de la balise <abbr> permet
                                            ici d'expliciter une notation raccourcie
                                        -->
                                        <abbr title="<?= $time->format('%i minutes et %s secondes') ?>">
                                            <?= $time->format("%I'%S''") ?>
                                        </abbr>
                                    </li>
                                <?php
                                }
                                ?>
                            </ol>
                        <?php
                        }
                        ?>
                        <div class="text-center mt-4">
                            <button class="game-start-button btn btn-primary btn-lg" type="button">
                                Jouer
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Plateau de jeu -->
                <div class="game-board" aria-hidden="true">

                    <!-- Cartes -->
                    <div class="row g-0">
                        <?php
                        /**
                         * Nouveau type d'objet, nouveau manager !
                         * On récupère 8 cartes aléatoirement depuis la base
                         * de données.
                         */
                        $pokemons = PokemonManager::findRandom(8);

                        /**
                         * Puis on duplique la liste des cartes sélectionnées
                         * et on mélange le tout !
                         * 
                         * Étant donné que la liste d'objets retournée par le
                         * manager n'est pas un tableau associatif, `array_merge()`
                         * se contentera de créer un nouveau tableau contenant
                         * deux fois les éléments de notre liste.
                         * 
                         * Attention : la fonction `shuffle()`, elle, modifie
                         * directement le tableau qui lui est passé !
                         * 
                         * @see https://www.php.net/manual/fr/function.array-merge.php
                         * @see https://www.php.net/manual/fr/function.shuffle.php
                         */
                        $cards = array_merge($pokemons, $pokemons);
                        shuffle($cards);

                        foreach ($cards as $index => $pokemon) {

                            $name = $pokemon->getName();

                        ?>
                            <!--
                                Les cartes comportent beaucoup d'attributs, pour des <div> !
                                
                                La classe HTML `pokemon-card` sera utilisée en JavaScript pour
                                identifier les cartes.
                                L'attribut personnalisé `data-pokemon` servira également en
                                JavaScript, notamment pour identifier le Pokémon de chaque
                                carte et ainsi vérifier la correspondance entre deux cartes qui
                                seront retournées.
                                L'attribut `title` sert à afficher une info-bulle au survol
                                de la souris ainsi qu'à donner une indication à certaines
                                technologies d'assistance pour indiquer à l'utilisateur le
                                résultat prévisible du clic (et aussi l'inciter un peu à
                                cliquer, c'est vrai...).
                                L'attribut `tabindex` sert à rendre les <div> accessibles
                                au clavier... parce qu'elles ne sont pas des éléments
                                interactifs par défaut !
                                Enfin, les attributs `role`, `aria-controls` et `aria-expanded`
                                servent à décrire le comportement de la <div> à certaines
                                technologies d'assitance, afin que celles-ci puissent le
                                restituer convenablement à leurs utilisateurs.
                            -->
                            <div class="pokemon-card col-3 col-md-3 my-2" data-pokemon="<?= $name ?>" title="Découvrir le Pokémon" tabindex="0" role="button" aria-controls="<?= "$name-$index" ?>" aria-expanded="false">
                                <!--
                                    Cette <div> sert de cadre aux deux faces d'une même carte.
                                -->
                                <div class="pokemon-card-frame" id="<?= "$name-$index" ?>">
                                    <!-- Face de la carte -->
                                    <div class="pokemon-card-front">
                                        <img src="<?= $pokemon->getImageUrl() ?>" alt="<?= $pokemon->getDisplayName() ?>" title="<?= $pokemon->getDisplayName() ?>" />
                                    </div>
                                    <!-- Dos de la carte -->
                                    <div class="pokemon-card-back" aria-hidden="true"></div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>

                    <!-- Compteur et jauge de temps -->
                    <div class="d-grid mx-5">
                        <meter class="game-board-timer" id="timer-meter" min="0" max="1" high="0" low="0.3" optimum="0.4" value="1">
                            Il te reste
                            <span class="game-board-timer-alt">
                                <!-- Le texte alternatif à la jauge sera rempli et actualisé via JavaScript -->
                            </span>
                        </meter>
                        <label for="timer-meter" class="game-board-timer-label form-label text-center">
                            <!-- Le label sera rempli et actualisé via JavaScript -->
                        </label>
                    </div>
                </div>

                <!-- Écran de fin du jeu -->
                <div class="game-end" hidden>

                    <!-- Écran de victoire (sera masqué en cas de défaite) -->
                    <div class="game-end-victory text-center">
                        <h1 class="mb-3">
                            Victoire !
                        </h1>
                        <div class="game-end-victory-spinner" role="status">
                            Enregistrement de votre score en cours&hellip;
                            <div class="game-end-victory-spinner-rail" aria-hidden="true">
                                <img class="game-end-victory-spinner-ball" src="/img/poke-ball.png" alt="Pm" />
                            </div>
                        </div>
                        <a class="game-end-victory-again btn btn-primary btn-lg" href="/" hidden>
                            Rejouer
                        </a>
                    </div>

                    <!-- Écran de défaite (sera masqué en cas de victoire) -->
                    <div class="game-end-defeat">
                        <h1>
                            Défaite&hellip;
                        </h1>
                        <a class="btn btn-primary btn-lg" href="/">
                            Rejouer
                        </a>
                    </div>
                </div>
            </div>
        </main>
        <footer class="my-5">
            <nav class="row text-center">
                <div class="col">
                    <a href="https://github.com/Puxarnal/Pokemory" target="_blank">
                        Voir sur GitHub
                    </a>
                </div>
            </nav>
        </footer>
    </div>

    <script src="/build/js/game.js" async></script>
</body>

</html>
