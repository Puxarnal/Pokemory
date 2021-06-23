<?php
    require_once __DIR__ . '/../src/boot.php';

    use Pokemory\Manager\ScoreManager;

    $page_title = 'Accueil';
    include __DIR__ . '/../resources/templates/header.php';
?>

<div class="row align-items-center my-4">
    <div class="col-12 col-md">
        <div class="d-grid d-md-block text-center">
            <a class="btn btn-primary btn-lg p-3 px-md-5" href="/play.php">
                Jouer
            </a>
        </div>
    </div>
    <div class="col-12 col-md">
        <section class="mt-4 mt-md-0">
            <h2>
                Meilleurs temps
            </h2>
            <?php
                $best_scores = ScoreManager::findBestScores(10);

                if (count($best_scores) == 0) {
                    ?>
                    <p>
                        Il semblerait que personne n'a encore joué à Pokémory.
                        Ça vous dit de
                        <a href="/play.php" title="Jouer à Pokémory">tenter votre chance</a>
                        ?
                    </p>
                    <?php
                } else {
                    ?>
                    <ol>
                        <?php
                            foreach ($best_scores as $score) {
                                ?>
                                <li>
                                    <b>
                                        <?= $score->getPseudonym() ?>
                                    </b>
                                    -
                                    <?= $score->getTime()->format() ?>
                                </li>
                                <?php
                            }
                        ?>
                    </ol>
                    <?php
                }
            ?>
        </section>
    </div>
</div>

<?php
    include __DIR__ . '/../resources/templates/footer.php';
?>
