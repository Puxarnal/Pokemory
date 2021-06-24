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
         * Configuration des étapes du jeu
         * La propriété `element` permet d'accéder directement
         * à l'élément du DOM dans lequel se déroule l'étape.
         * La propriété `discard` indique si l'élément doit être
         * complètement masqué lorsqu'on quitte l'étape.
         */
        steps: {
            start: {
                element: document.querySelector('.game-start'),
                discard: true,
            },
            board: {
                element: document.querySelector('.game-board'),
                discard: false,
                init: () => {
                    board.startTime = Date.now()
                    board.timerId = setInterval(
                        updateTimer,
                        config.board.timerUpdateInterval
                    )
                }
            },
            end: {
                element: document.querySelector('.game-end'),
                discard: true,
                init: () => {
                    if (board.remainingCards > 0) {
                        handleDefeat()
                    } else {
                        handleVictory()
                    }
                }
            },
        },
        /**
         * Configuration du plateau de jeu
         */
        board: {
            /**
             * Délai en millisecondes avant que deux cartes qui ne
             * matchent pas se retournent automatiquement
             */
            unrevealDelay: 600,
            /**
             * Intervalle en millisecondes auquel le timer est
             * mis à jour
             */
            timerUpdateInterval: 400
        },
    };

    /**
     * Représente le score du joueur qui sera envoyé au
     * serveur pour être enregistré en base de données
     * si le joueur parvient à terminer le jeu dans le
     * temps imparti
     */
    const score = {
        player: '',
        time: 0,
    };

    /**
     * Passe à une étape du jeu
     * @param {String} name Le nom de l'étape à laquelle passer
     */
    const goToStep = (name) => {
        const step = config.steps[name];
        if (step) {
            if (step.init) {
                step.init()
            }
            step.element.hidden = false;
            step.element.setAttribute('aria-hidden', 'false');
        }
    };

    /**
     * Quitte une étape du jeu
     * @param {String} name Le nom de l'étape à quitter
     */
    const discardStep = (name) => {
        const step = config.steps[name];
        if (step) {
            step.element.hidden = step.discard;
            step.element.setAttribute('aria-hidden', 'true');
        }
    };

    /**
     * Gestion de la première étape du jeu
     */
    config.steps.start.element.querySelector('.game-start-form')
        // Lorsque l'utilisateur valide son pseudo...
        .addEventListener('submit', (event) => {
            // ...on empêche le formulaire d'être envoyé au serveur...
            event.preventDefault();
            // ...on renseigne le pseudo du joueur...
            score.player = event.target.elements.pseudo.value;
            // ...on quitte la première étape...
            discardStep('start');
            // ...et on passe à la suivante ;-)
            goToStep('board');
        });

    /**
     * Classe qui identifie les cartes
     */
    const baseClass = 'pokemon-card';

    /**
     * Classe qui indique qu'une carte est face visible
     */
    const revealedClass = `${baseClass}-revealed`;

    /**
     * Retourne une carte face visible
     * @param {Element} card L'élément DOM représentant la carte
     */
    const reveal = (card) => {
        card.classList.add(revealedClass);
        card.setAttribute('aria-expanded', 'true');
    };

    /**
     * Retourne une carte face cachée
     * @param {Element} card L'élément DOM représentant la carte
     */
    const unreveal = (card) => {
        card.classList.remove(revealedClass);
        card.setAttribute('aria-expanded', 'false');
    };

    /**
     * Vérifie si une carte est face visible
     * @param {Element} card L'élément DOM représentant la carte
     * @returns {Boolean} `true` si la carte est face visible, `false` sinon
     */
    const isRevealed = (card) => card.classList.contains(revealedClass);

    /**
     * Carte qui indique qu'une carte est verrouillée
     */
    const lockedClass = `${baseClass}-locked`;

    /**
     * Verrouille une carte, la rendant impossible à retourner
     * @param {Element} card L'élément DOM représentant la carte
     */
    const lock = (card) => {
        card.classList.add(lockedClass);
    };

    /**
     * Vérifie si une carte est verrouillée
     * @param {Element} card L'élément DOM représentant la carte
     * @returns {Boolean} `true` si la carte est verrouillée, `false` sinon
     */
    const isLocked = (card) => card.classList.contains(lockedClass);

    /**
     * Toutes les cartes
     */
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
        ellapsedTime: 0
    };

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
        if (board.remainingCards == 0 || board.ellapsedTime > config.maxAllowedTime) {
            // ...on arrête le timer
            clearInterval(board.timerId)
            // ...on enregistre le temps du joueur en secondes
            score.time = Math.ceil(board.ellapsedTime / 1000)
            // ...et on passe à la dernière étape
            goToStep('end')
        }
    }


    const timer = config.steps.board.element.querySelector('.game-board-timer')
    const timerLabel = timer.querySelector('.game-board-timer-label')

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

        const remainingSeconds = remainingTime / 1000;
        
        // on décompose le temps restant en minutes et secondes...
        const seconds = remainingSeconds % 60
        const minutes = (remainingSeconds - seconds) / 60

        // ...on formate avec les zéro initiaux...
        const formatedSeconds = ('0' + seconds).slice(-2)
        const formatedMinutes = ('0' + minutes).slice(-2)

        // ...et on met à jour le libellé de la jauge
        timerLabel.textContent = `${formatedMinutes}:${formatedSeconds}`

        // et enfin on n'oublie pas de lancer la vérification de fin de partie !
        checkGameState()
    }

    /**
     * Gestion du clic sur une carte
     */
    const handle = function () {
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
                    board.remainingCards-= 2

                    // ...et on lance la vérification de fin de partie
                    checkGameState()

                } else {
                    // ...mais si la carte ne correspond pas...

                    // ...on met le jeu "en pause" le temps de l'animation...
                    board.uiWaiting = true

                    // ...on diffère le retournement des deux cartes
                    setTimeout(
                        () => {
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

                        },
                        config.board.unrevealDelay
                    )
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

    // On attache le gestionnaire du clic aux cartes
    for (let i = 0, l = cards.length; i < l; i++) {
        cards[i].addEventListener('click', handle)
    }

    /**
     * Élément DOM représentant l'écran de victoire
     */
    const victory = config.steps.end.element.querySelector('.game-end-victory')
    /**
     * Élément DOM représentant l'écran de défaite
     */
    const defeat = config.steps.end.element.querySelector('.game-end-defeat')

    /**
     * Gestion de l'écran de victoire
     */
    const handleVictory = () => {
        // on n'affiche pas l'écran de défaite, hein !
        defeat.hidden = true

        // on tente de faire enregistrer le score par le serveur
        saveScore()
            // 
            // .then(TODO)
            // .catch(TODO)
            // on masque l'animation et on affiche le bouton "Rejouer"
            .finally(() => {
                victory.querySelector('.game-end-victory-spinner').hidden = true;
                victory.querySelector('.game-end-victory-again').hidden = false
            })
    }

    /**
     * Envoie une requête asynchrone au serveur pour enregistrer
     * le score du joueur en base de données
     * @returns {Promise}
     */
    const saveScore = () => {
        const payload = new FormData()
        payload.append('pseudonym', score.player)
        payload.append('time', score.time)

        return fetch('/save-score.php', {
            method: 'post',
            body: payload,
        })
    }

    /**
     * Gestion de l'écran de défaite
     */
    const handleDefeat = () => {
        victory.hidden = true
    }

})()

