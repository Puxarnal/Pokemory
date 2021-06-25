<?php

namespace Pokemory\Manager;

use Pokemory\Model\Pokemon;

class PokemonManager extends BaseManager
{
    /**
     * Enregistre un Pokemon en base de données
     * @param Pokemon $pokemon
     * @return bool
     */
    public static function save($pokemon): bool
    {
        // On récupère une connexion à la base de données
        $connection = static::getConnection();

        if (!$connection) {
            /**
             * Si la connextion est impossible, on donne un
             * retour d'échec du traitement
             */
            return false;
        }

        /**
         * On prépare les données qui seront enregistrées
         * en les extrayant directement de l'objet passé
         * en paramètre de la méthode
         */
        $data = [
            'name' => $pokemon->getName(),
            'fr' => $pokemon->getDisplayName(),
            'img' => $pokemon->getImageUrl()
        ];

        /**
         * Notre système de gestion des objets est fait de
         * telle façon qu'un objet a un ID seulement s'il
         * est issu de la base de données.
         * 
         * Donc si l'objet semble être déjà enregistré en base...
         */
        if ($pokemon->getId()) {
            /**
             * On s'apprête à exécuter une requête UPDATE
             * 
             * Plutôt qu'écrire la requête en y intégrant les
             * valeurs finales directement, nous utilisons ici
             * une requête préparée.
             * L'avantage de ce procédé est qu'en écrivant la
             * requête SQL avec des marqueurs de substitution
             * à la place des valeurs, on n'a pas à se soucier
             * de l'échappement de ces valeurs (en particulier
             * des chaînes).
             * Les valeurs seront échappées au moment où nous
             * les passeront à la requête pour qu'elle soit
             * exécutée.
             * 
             * @see https://www.php.net/manual/fr/pdo.prepare.php
             */
            $query = $connection->prepare(
                /**
                 * Attention : ne jamais oublier la clause WHERE
                 * sur une requête UPDATE, au risque de modifier
                 * absolument toutes les lignes de la table...
                 * 
                 * (#C'estDuVécu)
                 */
                'UPDATE `pokemons` SET `name` = :name, `fr` = :fr, `img` = :img WHERE `id` = :id'
            );

            /**
             * En donnant la valeur d'un marqueur de substitution
             * avant d'exécuter la requête, on n'aura pas à le
             * passer au moment de l'exécution.
             * Ici, on passe la valeur du paramètre `id` car elle
             * ne fait pas partie du tableau des données que nous
             * nous avons extraites de notre objet.
             * 
             * @see https://www.php.net/manual/fr/pdostatement.bindvalue.php
             */
            $query->bindValue('id', $pokemon->getId());

            /**
             * On lance la requête SQL en passant le tableau
             * de valeurs ; PDO les échappera et les substituera
             * avant d'exécuter le SQL et d'indiquer la
             * réussite en renvoyant un booléen.
             * 
             * @see https://www.php.net/manual/fr/pdostatement.execute.php
             */
            return $query->execute($data);
        }

        /**
         * Dans le cas où l'objet n'a pas d'ID (c'est-à-dire
         * qu'il ne semble pas issu de la base de données),
         * on optera pour une requête INSERT afin de créer un
         * nouvel enregistrement.
         * 
         * Là encore, nous utilisons une requête préparée, que
         * nous exécuterons avec les données extraites de
         * l'objet.
         */
        $query = $connection->prepare(
            'INSERT INTO `pokemons` (`name`, `fr`, `img`) VALUES (:name, :fr, :img)'
        );

        // Si la requête est bien exécutée...
        if ($query->execute($data)) {
            /**
             * ...on utilise une méthode spéciale de la classe parente pour
             * renseigner l'ID de l'objet.
             * 
             * Avec PDO, lorsqu'on exécute une requête INSERT, il est
             * possible de récupérer la valeur de la clé primaire de
             * la dernière ligne insérée en base avec la méthode
             * lastInsertId().
             * Attention toutefois, s'il vous arrive de créer plusieurs
             * nouvelles lignes en une seule requête INSERT, PDO ne
             * sera capable de vous donner via cette méthode que la
             * valeur de la clé pour la première ligne qui aura été
             * insérée.
             * 
             * @see https://www.php.net/manual/fr/pdo.lastinsertid.php
             */
            static::setModelProperties($pokemon, ['id' => $connection->lastInsertId()]);

            // On retourne `true` pour indiquer que le traitement a abouti
            return true;
        }

        return false;
    }

    /**
     * Supprime un Pokémon de la base de données
     * @param Pokemon $pokemon
     * @return bool
     */
    public static function delete($pokemon): bool
    {
        // Si l'objet ne semble pas exister en base...
        if (!$pokemon->getId()) {
            /**
             * ...on renvoie `false` pour indiquer que la
             * suppression n'a pas pu aboutir
             * (sans ID, difficile de savoir quoi effacer !)
             */
            return false;
        }

        // On tente de récupérer une connexion à la base de données
        $connection = static::getConnection();

        if (!$connection) {
            /**
             * Si la connexion est impossible, on indique que le
             * traitement n'a pas pu aboutir
             */
            return false;
        }

        /**
         * Le retour des requêtes préparées !
         * Cette fois-ci, on n'a besoin de d'une seule valeur :
         * l'identifiant de la ligne à supprimer.
         * 
         * Attention! Achtung! Warning!
         * Oublier la clause WHERE sur une requête UPDATE est
         * risqué, voire problématique, certes.
         * Oublier la clause WHERE sur une requête DELETE est
         * catastrophique. En effet, si vous ne spécifiez pas
         * de clause WHERE vous supprimerez alors tous les
         * enregistrements de la table. De même si votre clause
         * WHERE n'est pas assez précise, vous supprimerez
         * potentiellement beaucoup plus de données que ce que
         * vous souhaitiez supprimer à l'origine.
         * 
         * (#C'estDuVécuParQuelqu'unD'Autre)
         */
        $query = $connection->prepare(
            'DELETE FROM `pokemons` WHERE `id` = :id'
        );

        // on renvoie l'état de réussite de la requête
        return $query->execute(['id' => $pokemon->getId()]);
    }

    /**
     * Récupère un nombre de Pokémons aléatoires en base de données
     * @param int $limit Le nombre de Pokémons à récupérer
     * @return Pokemon[]
     */
    public static function findRandom(int $limit)
    {
        // On tente de récupérer une connexion à la base de données
        $connection = static::getConnection();

        // Si la connexion est impossible...
        if (!$connection) {
            /**
             * ...je choisis de renvoyer une liste vide afin que le
             * code qui appelle cette méthode puisse avoir une chance
             * de donner un retour convenable à l'utilisateur
             */
            return [];
        }

        /**
         * Les requêtes préparées, volume 3
         * Cette fois-ci on s'attaque au SELECT.
         * Rien de sorcier par rapport aux autres requêtes rencontrées
         * plus haut.
         * On notera tout de même que les fonctions SQL (comme RAND() ici)
         * doivent être écrites dans la requêtes, car si elles sont
         * passées comme valeur d'un marqueur de substitution, elles seront
         * échappées comme des chaînes de caractères.
         */
        $query = $connection->prepare(
            'SELECT `id`, `name`, `fr`, `img` FROM `pokemons` ORDER BY RAND() LIMIT :limit'
        );

        /**
         * Il est possible de préciser à PDO de quelle manière
         * il devrait échapper certaines valeurs.
         * Ici, en SQL, la clause LIMIT attend obligatoirement
         * un entier ; or, PDO échappe par défaut toutes les
         * valeurs comme des chaînes ; il est donc nécessaire,
         * pour utiliser LIMIT dans une requête préparée, de
         * préciser à PDO que la valeur donnée à la clause
         * doit être échappée traitée comme un entier.
         * 
         * Ainsi, on obtiendra bien dans la requête :
         *      LIMIT 10
         * Et non, comme c'est le cas pas défaut :
         *      LIMIT '10'
         */
        $query->bindValue('limit', $limit, \PDO::PARAM_INT);

        /**
         * On lance l'exécution de la requête, sans paramètres
         * cette fois puisque nous avons déjà lié notre
         * unique paramètre à la requête.
         * 
         * Si la requête échoue...
         */
        if (!$query->execute()) {
            /**
             * ...on renvoie une liste vide
             * 
             * Idéalement, il faudrait ici garder une trace des
             * éventuelles erreurs renvoyées par la base de
             * données...
             */
            return [];
        }

        /**
         * La requête a abouti, il est temps d'instancier nos objets
         * à partir des données recupérées !
         * 
         * Étant donné qu'on s'apprête à retourner plusieurs
         * objets, on prépare une liste...
         */
        $pokemons = [];

        /**
         * ...puis on itère sur chaque ligne renvoyée par la base
         * 
         * La façon d'écrire cette boucle peut paraître étrange
         * de prime abord, mais elle s'explique !
         * La méthode fetch() retourne à chaque fois qu'on l'appelle
         * la ligne de résultat suivante (première ligne au premier
         * appel, deuxième ligne au deuxième appel, etc.).
         * Une fois que la dernière ligne a été retournée, la méthode
         * ne retourne plus que `false`.
         * Ceci est fait pour permettre de ne traiter qu'une seule
         * ligne à la fois, en évitant que PHP doive récupérer
         * de très gros volumes de données en une seule fois et
         * sature potentiellement la mémoire qui lui est allouée.
         * Bien entendu, vous pouvez toujours garder tous vos
         * résultats en mémoire une fois que vous les aurez tous
         * lus ; mais dans ce cas, les soucis d'allocation de
         * mémoire seront votre responsabilité et non celle de
         * PDO.
         * 
         * @see https://www.php.net/manual/fr/pdostatement.fetch.php
         */
        while ($row = $query->fetch()) {
            // Pour chaque ligne de résultat on instancie un nouvel objet...
            $pokemon = new Pokemon();

            /**
             * ...puis on renseigne toute ses propriétés en appelant une méthode
             * spéciale de la classe parente
             */
            static::setModelProperties($pokemon, [
                'id' => $row['id'],
                'name' => $row['name'],
                'fr' => $row['fr'],
                'img' => $row['img']
            ]);

            // On ajoute l'objet fraîchement créé à la liste
            $pokemons[] = $pokemon;
        }

        // On retourne la liste de nos objets
        return $pokemons;
    }
}
