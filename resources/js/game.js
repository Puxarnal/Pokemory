/**
 * Toute l'initialisation du jeu se fait dans
 * une IIFE (une fonction qui est appelée
 * immédiatement après sa définition). Ceci
 * permet de ne pas exposer les variables qui
 * seront définies dans son scope et donc de
 * garder le fonctionnement du jeu dans une
 * sorte de "boîte noire".
 * 
 * @see https://developer.mozilla.org/fr/docs/Glossary/IIFE
 */
(() => {
    /**
     * Configuration du jeu
     */
    const config = {
        /**
         * Temps maximum imparti
         */
        maxAllowedTime: 3 * 60 * 1000,
        /**
         * Configuration des écrans du jeu
         * La propriété `element` permet d'accéder directement
         * à l'élément du DOM représentant l'écran.
         * La propriété `discard` indique si l'élément doit être
         * complètement masqué lorsqu'on quitte l'écran.
         * Si la propriété facultative `init` peut être une
         * fonction qui sera exécutée avant l'affichage de
         * l'écran.
         */
        steps: {
            // Premier écran (avant la partie)
            start: {
                element: document.querySelector('.game-start'),
                discard: true,
            },
            // Deuxième écran (jeu)
            board: {
                element: document.querySelector('.game-board'),
                discard: false,
                // Avant de passer à l'écran du jeu...
                init: () => {
                    /**
                     * ...on garde en mémoire le timestamp du
                     * début de la partie (il nous servira à
                     * calculer la durée du jeu)
                     */
                    board.startTime = Date.now()
                    /**
                     * ...on initialise le timer qui mettra à
                     * jour la jauge de temps et vérifiera que
                     * le joueur n'a pas dépensé tout le temps
                     * qui lui était imparti
                     *
                     * La fonction `setInterval()` retourne un
                     * identifiant qui nous servira, à la fin
                     * du jeu, à arrêter le timer.
                     *
                     * @see https://developer.mozilla.org/en-US/docs/Web/API/WindowOrWorkerGlobalScope/setInterval
                     */
                    board.timerId = setInterval(
                        updateTimer,
                        config.board.timerUpdateInterval
                    )
                },
            },
            // Dernier écran (fin de partie)
            end: {
                element: document.querySelector('.game-end'),
                discard: true,
                // Avant de passer au dernier écran...
                init: () => {
                    // ...s'il reste des cartes non retournées...
                    if (board.remainingCards > 0) {
                        // ...alors on initialise l'écran de défaite
                        handleDefeat()
                    } else {
                        // ...sinon, on initialise l'écran de victoire
                        handleVictory()
                    }
                },
            },
        },
        /**
         * Configuration du plateau de jeu
         */
        board: {
            /**
             * Délai en millisecondes avant que deux cartes
             * retournées qui ne correspondent pas se
             * retournent automatiquement
             */
            unrevealDelay: 600,
            /**
             * Intervalle en millisecondes auquel le timer est
             * mis à jour
             */
            timerUpdateInterval: 400,
        },
    }

    /*
     * ==================================================
     *  Gestion de la navigation entre écrans
     * ==================================================
     */

    /**
     * Passe à une étape du jeu
     * @param {String} name Le nom de l'étape à laquelle passer
     */
    const goToStep = (name) => {
        const step = config.steps[name]
        if (step) {
            if (step.init) {
                step.init()
            }
            step.element.hidden = false
            step.element.setAttribute('aria-hidden', 'false')
        }
    }

    /**
     * Quitte une étape du jeu
     * @param {String} name Le nom de l'étape à quitter
     */
    const discardStep = (name) => {
        const step = config.steps[name]
        if (step) {
            step.element.hidden = step.discard
            step.element.setAttribute('aria-hidden', 'true')
        }
    }

    /*
     * ==================================================
     *  Gestion du jeu
     * ==================================================
     */

    /**
     * Classe qui identifie les cartes
     */
    const baseClass = 'pokemon-card'

    /**
     * Classe qui indique qu'une carte est face visible
     */
    const revealedClass = `${baseClass}-revealed`

    /**
     * Retourne une carte face visible
     * @param {Element} card L'élément DOM représentant la carte
     */
    const reveal = (card) => {
        card.classList.add(revealedClass)
        card.setAttribute('aria-expanded', 'true')
    }

    /**
     * Retourne une carte face cachée
     * @param {Element} card L'élément DOM représentant la carte
     */
    const unreveal = (card) => {
        card.classList.remove(revealedClass)
        card.setAttribute('aria-expanded', 'false')
    }

    /**
     * Vérifie si une carte est face visible
     * @param {Element} card L'élément DOM représentant la carte
     * @returns {Boolean} `true` si la carte est face visible, `false` sinon
     */
    const isRevealed = (card) => card.classList.contains(revealedClass)

    /**
     * Carte qui indique qu'une carte est verrouillée
     */
    const lockedClass = `${baseClass}-locked`

    /**
     * Verrouille une carte, la rendant impossible à retourner
     * @param {Element} card L'élément DOM représentant la carte
     */
    const lock = (card) => {
        card.classList.add(lockedClass)
    }

    /**
     * Vérifie si une carte est verrouillée
     * @param {Element} card L'élément DOM représentant la carte
     * @returns {Boolean} `true` si la carte est verrouillée, `false` sinon
     */
    const isLocked = (card) => card.classList.contains(lockedClass)

    // Toutes les cartes du plateau
    const cards = config.steps.board.element.getElementsByClassName(baseClass)

    /**
     * État du plateau de jeu
     */
    const board = {
        /**
         * Nombre de carte restant à découvrir
         */
        remainingCards: cards.length,
        /**
         * Carte actuellement retournée
         */
        current: null,
        /**
         * Témoin indiquant si le jeu est actuellement bloqué (intentionnellement)
         */
        uiWaiting: false,
        /**
         * Timestamp du commencement de la partie
         */
        startTime: 0,
        /**
         * Identifiant du timer qui calcule le temps
         */
        timerId: 0,
        /**
         * Nombre de millisecondes écoulées depuis le début de la partie
         */
        ellapsedTime: 0,
    }

    /**
     * Vérifie si la partie est terminée et, le cas échéant, arrête le timer
     * et passe à la dernière étape du jeu.
     */
    const checkGameState = () => {
        /**
         * Pour vérifier si la partie est terminée, on vérifie si au moins une
         * des conditions suivantes est remplie :
         *
         *  -   Toutes les cartes ont été déouvertes, c'est-à-dire qu'il ne reste
         *      plus de carte à découvrir
         *  -   Le temps imparti est écoulé, c'est-à-dire que la différence entre
         *      le timestamp du début de la partie et le timestamp actuel est
         *      supérieur au temps maximum autorisé
         *
         * Si la partie est terminée...
         */
        if (
            board.remainingCards == 0 ||
            board.ellapsedTime > config.maxAllowedTime
        ) {
            // ...on arrête le timer
            clearInterval(board.timerId)
            // ...et on passe à la dernière étape
            goToStep('end')
        }
    }

    /**
     * Gère le clic sur une carte
     */
    const handleCardClick = function () {
        /**
         * Ici, `this` aura pour valeur l'élément DOM représentant la carte
         * sur laquelle l'utilisateur aura cliqué.
         * On utilisera une variable du nom `card` dans toute la suite du
         * traitement, parce que c'est quand même beaucoup plus explicite
         * que `this`, non ?
         */
        const card = this

        /**
         * Si on a volontairement bloqué le rendu du jeu (cas où deux cartes
         * non matchées sont visibles et attendent d'être automatiquement
         * cachées)
         * ou si la carte sur laquelle l'utilisateur vient de cliquer est
         * verrouillée (cas d'une carte déjà matchée)...
         */
        if (board.uiWaiting || isLocked(card)) {
            /**
             * ...alors on ne fait rien !
             * Ceci porte le nom barbare de "early exit" (traduisez à peu près
             * par "sortir tôt"). Comme son nom l'indique, ça permet de
             * court-circuiter et quitter l'appel de fonction lorsqu'on se
             * trouve dans une situation où on n'a rien à faire.
             * Ici, la condition identifie les deux seuls cas où cette fonction
             * ne doit rien faire du tout. Utiliser l'early exit permet de
             * faire l'économie d'un gros `if` qui engloberait tout le reste
             * du traitement comme ceci :
             *
             * if (!board.uiWaiting && !isLocked(card)) {
             *     if (isRevealed(card)) {
             *         // ...
             *     } else {
             *         // ...
             *     }
             * }
             *
             * Bien que ça ne change pas grand-chose en termes de performances,
             * certains argueront qu'on gagne en lisibilité puisqu'on n'a pas
             * besoin de lire tout le corps de la fonction pour savoir qu'il ne
             * se passera rien dans ce cas-là.
             *
             * Si vous avez la foi, essayer donc de réécrire le reste de cette
             * fonction avec un peu d'early exit ! ;-)
             */
            return
        }

        // Si l'utilisateur vient de cliquer sur une carte visible...
        if (isRevealed(card)) {
            // ...on cache la carte...
            unreveal(card)
            // ...et on indique au jeu qu'il n'y a plus de carte en attente de match
            board.current = null
        } else {
            /**
             * Mais dans le cas où l'utilisateur vient de cliquer sur
             * une carte face cachée, on la retourne face visible
             */
            reveal(card)

            // Si une carte était déjà face visible...
            if (board.current) {
                // ...et si la carte qui vient d'être retournée correspond...
                if (card.dataset.pokemon == board.current.dataset.pokemon) {
                    // ...on verrouille les deux cartes
                    lock(card)
                    lock(board.current)

                    // ...on indique au jeu qu'il n'y a plus de carte en attente de match
                    board.current = null
                    // ...on décrémente le nombre de cartes restant à matcher
                    board.remainingCards -= 2

                    // ...et on lance la vérification de fin de partie
                    checkGameState()
                } else {
                    // ...mais si la carte ne correspond pas...

                    // ...on met le jeu "en pause" le temps de l'animation...
                    board.uiWaiting = true

                    // ...on diffère le retournement des deux cartes
                    setTimeout(() => {
                        /**
                         * Après avoir attendu un peu (histoire que le
                         * joueur ait vu les Pokémons, quand même !),
                         * on cache les deux cartes...
                         */
                        unreveal(card)
                        unreveal(board.current)

                        // ...et on n'oublie pas d'indiquer au jeu qu'il n'a plus de carte en attente de match
                        board.current = null
                        // ...et on n'oublie pas non plus d'enlever le "mode pause" (celle-ci est tricky)
                        board.uiWaiting = false
                    }, config.board.unrevealDelay)
                }
            } else {
                /**
                 * Si aucune carte n'était face visible, on se contente
                 * d'indiquer au jeu qu'il y a désormais une carte en
                 * attente de match
                 */
                board.current = card
            }
        }
    }

    /*
     * ==================================================
     *  Gestion du timer
     * ==================================================
     */

    // Élément représentant la jauge du timer (<meter>)
    const timer = config.steps.board.element.querySelector('.game-board-timer')

    // Label de la jauge du timer
    const timerLabel = config.steps.board.element.querySelector('.game-board-timer-label')

    // Élément contenant le texte alternatif pour la balise <meter>
    const timerAlt = timer.querySelector('.game-board-timer-alt')

    const updateTimer = () => {
        /**
         * On calcule le temps écoulé depuis le début de la partie,
         * puis on le convertit en secondes (parce que JavaScript est
         * précis à la milliseconde mais pas nos calculs)
         */
        board.ellapsedTime = Date.now() - board.startTime

        // on calcule le temps restant
        const remainingTime = config.maxAllowedTime - board.ellapsedTime

        /**
         * On met à jour la jauge de temps en indiquant le ratio de
         * temps qu'il reste au joueur.
         * Utiliser un ratio plutôt que les vraies valeurs nous
         * permet de modifier facilement le temps maximum dans le
         * JavaScript sans avoir à le modifier également dans le
         * code HTML. ;-)
         */
        timer.value = remainingTime / config.maxAllowedTime

        const remainingSeconds = remainingTime / 1000

        // on décompose le temps restant en minutes et secondes...
        const seconds = Math.ceil(remainingSeconds % 60)
        const minutes = Math.ceil((remainingSeconds - seconds) / 60)

        // ...on formate avec les zéro initiaux...
        const formatedSeconds = ('0' + seconds).slice(-2)
        const formatedMinutes = ('0' + minutes).slice(-2)

        // ...et on met à jour le libellé et le texte alternatif à la jauge
        timerAlt.textContent = timerLabel.textContent = `${formatedMinutes}:${formatedSeconds}`

        // et enfin on n'oublie pas de lancer la vérification de fin de partie !
        checkGameState()
    }

    /*
     * ==================================================
     *  Gestion de la victoire ou de la défaite
     * ==================================================
     */

    // Élément DOM représentant l'écran de victoire
    const victory = config.steps.end.element.querySelector('.game-end-victory')

    // Élément DOM représentant l'écran de défaite
    const defeat = config.steps.end.element.querySelector('.game-end-defeat')

    /**
     * Gestion de l'écran de victoire
     */
    const handleVictory = () => {
        // on n'affiche pas l'écran de défaite, hein !
        defeat.hidden = true

        // on tente de faire enregistrer le score par le serveur
        saveScore()
            // .then(TODO)
            // .catch(TODO)
            // on masque l'animation et on affiche le bouton "Rejouer"
            .finally(() => {
                victory.querySelector(
                    '.game-end-victory-spinner'
                ).hidden = true
                victory.querySelector('.game-end-victory-again').hidden = false
            })
    }

    /**
     * Gestion de l'écran de défaite
     */
    const handleDefeat = () => {
        // on n'afiche pas l'écran de victoire :-(
        victory.hidden = true
    }

    /*
     * ==================================================
     *  Gestion de l'enregistrement du score
     * ==================================================
     */

    /**
     * Envoie une requête asynchrone au serveur pour enregistrer
     * le score du joueur en base de données
     * @returns {Promise}
     */
    const saveScore = () => {
        const payload = new FormData()
        payload.append('time', board.ellapsedTime / 1000)

        return fetch('/save-score.php', {
            method: 'post',
            body: payload,
        })
    }

    /*
     * ==================================================
     *  Initialisation du jeu
     * ==================================================
     */

    config.steps.start.element
        .querySelector('.game-start-button')
        // Lorsque l'utilisateur clique sur le bouton du premier écran...
        .addEventListener('click', () => {
            // ...on quitte le premier écran
            discardStep('start')
            // ...et on passe au suivant !
            goToStep('board')
        })

    // On attache le gestionnaire du clic aux cartes
    for (let i = 0, l = cards.length; i < l; i++) {
        cards[i].addEventListener('click', handleCardClick)
    }

})()

