<?php
/**
 * Configuration de la connexion à la
 * base de données
 * 
 *  -   driver :    driver PDO pour le type de SGBD utilisé
 *  -   host :      adresse réseau du serveur de bases de données,
 *                  peut être localhost, 127.0.0.1 ou ::1 en local
 *  -   port :      port sur lequel le serveur de bases de données
 *                  accepte les connexions
 *  -   database :  nom de la base de données du projet
 *  -   user :      nom d'utilisateur pour la connexion (login)
 *  -   password :  mot de passe de connexion
 */
return [
    'driver' => 'mysql',
    'host' => 'mariadb',
    'port' => 3306,
    'database' => 'pokemory',
    'user' => 'admin',
    'password' => 'admin'
];
