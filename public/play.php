<?php
    require_once __DIR__ . '/../src/boot.php';

    use Pokemory\Manager\PokemonManager;

    $page_title = 'Jeu';
    include __DIR__ . '/../resources/templates/header.php';
?>

<div>
    <section>
        <?php
            $pokemons = PokemonManager::findRandom(8);

            $cards = array_merge($pokemons, $pokemons);
            shuffle($cards);
        ?>
        <div class="row">
            <?php
                foreach ($cards as $idx => $pokemon) {
                    ?>
                    <article id="<?= "{$pokemon->getId()}-{$idx}" ?>"
                        class="col-2 col-md-3"
                        data-pokemon="<?= $pokemon->getName() ?>"
                    >
                        <figure>
                            <img src="<?= $pokemon->getImageUrl() ?>" alt="<?= $pokemon->getDisplayName() ?>" />
                            <figcaption>
                                <?= $pokemon->getDisplayName() ?>
                            </figcaption>
                        </figure>
                    </article>
                    <?php
                }
            ?>
        </div>
    </section>
</div>

<?php
    include __DIR__ . '/../resources/templates/footer.php';
?>