<?php
require_once __DIR__ . '/../src/boot.php';

use Pokemory\Manager\PokemonManager;

$page_title = 'Jeu';
include __DIR__ . '/../resources/templates/header.php';
?>

<div>
    <section>
        <div class="game" aria-live="polite">
            <!-- game start form -->
            <div class="game-start">
                <form class="game-start-form">
                    <div class="my-5">
                        <label for="pseudo" class="form-label">
                            Tout d'abord, quel est ton nom ?
                        </label>
                        <input type="text" class="form-control" id="pseudo" name="pseudo" placeholder="Red" required>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary btn-lg" type="submit">
                            Commencer
                        </button>
                    </div>
                </form>
            </div>
            <!-- game board -->
            <div class="game-board">
                <div class="d-grid mx-5">
                    <meter class="game-board-timer" min="0" max="1" high="0" low="0.3" optimum="0.4" value="1">
                        Il te reste
                        <span class="game-board-timer-label"></span>
                    </meter>
                </div>
                <div class="game-board row g-0" aria-hidden="true">
                    <?php
                    $pokemons = PokemonManager::findRandom(8);

                    $cards = array_merge($pokemons, $pokemons);
                    shuffle($cards);
                    foreach ($cards as $pokemon) {
                        $uniqid = uniqid('card');
                    ?>
                        <div class="col-3 col-md-3 my-2 pokemon-card" title="Ouvrir la Poké-ball" role="button" aria-controls="<?= $uniqid ?>" aria-expanded="false" data-pokemon="<?= $pokemon->getName() ?>" tabindex="0">
                            <div class="pokemon-card-frame" id="<?= $uniqid ?>">
                                <div class="pokemon-card-front">
                                    <img src="<?= $pokemon->getImageUrl() ?>" alt="<?= $pokemon->getDisplayName() ?>" title="<?= $pokemon->getDisplayName() ?>" />
                                </div>
                                <div class="pokemon-card-back" aria-hidden="true"></div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <!-- game end -->
            <div class="game-end" hidden>
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
                    <a class="game-end-victory-again btn btn-primary btn-lg" href="/play.php" hidden>
                        Rejouer
                    </a>
                </div>
                <div class="game-end-defeat">
                    <h1>
                        Défaite&hellip;
                    </h1>
                    <a class="btn btn-primary btn-lg" href="/play.php">
                        Rejouer
                    </a>
                </div>
            </div>
        </div>
    </section>

    <script src="/build/js/game.js" async></script>

</div>

<?php
include __DIR__ . '/../resources/templates/footer.php';
?>
