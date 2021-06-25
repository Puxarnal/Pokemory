<?php

namespace Pokemory\Manager;

use PDO;
use ReflectionProperty;

/**
 * Implémente certaines méthodes communes à tous les managers
 * @abstract
 * 
 * @see https://www.php.net/manual/fr/language.oop5.abstract.php
 */
abstract class BaseManager implements ManagerInterface
{
    /**
     * @property PDO $connection La connexion principale à la base de données
     * @static
     */
    private static $connection;

    /**
     * Retourne la connexion à la base de données.
     * Si aucune connexion n'a encore été établie, elle est créée.
     * @return PDO
     */
    protected static function getConnection()
    {
        // Si aucune connexion n'a été établie...
        if (!self::$connection) {
            /**
             * ...on tente d'en initier une
             * 
             * Remarque :
             * Les structures try / catch permettent d'intercepter
             * les erreurs qui surviennent sous la forme d'objets
             * qu'on nomme, en PHP, des "exceptions" (c'est le
             * nom de la classe qui les représente).
             * La remontée d'erreurs par exceptions est un
             * mécanisme largement répandu dans les
             * paradigmes de programmation orientée objet.
             * 
             * @see https://www.php.net/manual/fr/language.exceptions.php
             */
            try {
                // on importe la configuration de la connexion
                $config = require CONFIG_DIR . 'database.php';

                /**
                 * Pour se connecter à une base de données, PDO
                 * a besoin qu'on lui donne certaines informations
                 * sous la forme d'une chaîne de paramètres
                 * appelée Data Source Name (DSN).
                 * 
                 * @see https://www.php.net/manual/fr/pdo.construct.php
                 */
                $dsn = $config['driver'] . ':'
                    . 'host=' . $config['host'] . ';'
                    . 'dbname=' . $config['database'];

                /**
                 * La plupart du temps le port d'écoute du SGBD est facultatif
                 * car beaucoup de systèmes ont un port part défaut que
                 * les administrateurs ne modifient pas systématiquement.
                 */
                if (key_exists('port', $config)) {
                    $dsn.= ';port=' . $config['port'];
                }

                /**
                 * Instanciation de la connexion à la base de données
                 * 
                 * En créant un nouvel objet PDO avec les informations
                 * de connexion, PHP va tenter d'établir une connexion
                 * avec notre base. Si la connexion échoue, c'est là
                 * qu'une erreur sera levée sous la forme d'une exception,
                 * qui sera alors interceptée dans le bloc `catch`
                 * ci-dessous
                 */
                self::$connection = new PDO(
                    $dsn,
                    $config['user'],
                    $config['password']
                );

            } catch (\Exception $e) {
                /**
                 * Remarque :
                 * On pourrait ici laisser l'erreur arrêter toute
                 * l'exécution, ou on peut choisir d'en faire un
                 * "échec silencieux" et laisser une chance au code
                 * qui appelle la méthode de gérer l'absence de
                 * connexion à la base de données.
                 * Idéalement, on devrait mettre en place un système
                 * de journalisation (logs) pour ce type d'incident,
                 * afin d'en avoir une trace à des fins de debug ou
                 * de monitoring.
                 * 
                 * (On va pas se mentir quand même : si votre application
                 * ne parviens pas à se connecter à votre base de données,
                 * il y a des grandes chances pour qu'aucune de ses
                 * fonctionnalités ne soit utilisable...)
                 */
                return null;
            }
        }

        // on retourne la connexion
        return self::$connection;
    }

    /**
     * Renseigne des propriétés d'un objet par réflexion
     * @param object $model L'objet sur lequel renseigner des propriétés
     * @param array $props Un tableau dont les clés sont les noms des propriétés à renseigner
     * @return void
     */
    protected static function setModelProperties(object $model, array $props): void
    {
        /**
         * Attention sorcellerie !
         * La réflexion permet de faire un peu tout et n'importe
         * quoi avec les objets. Ici, elle est utilisée comme
         * moyen de donner des valeurs à des propriétés non
         * accessibles sans passer par des constructeurs ni
         * des accesseurs.
         * Ceci n'est présenté qu'à titre informatif pour évoquer
         * la réflexion, il ne s'agit en aucun cas d'un exemple
         * canonique.
         * 
         * @see https://www.php.net/manual/fr/book.reflection.php
         * @see https://fr.wikipedia.org/wiki/R%C3%A9flexion_(informatique)
         */
        foreach ($props as $name => $value) {
            $reflect = new ReflectionProperty($model, $name);
            $reflect->setAccessible(true);
            $reflect->setValue($model, $value);
        }
    }
}
