<?php

/**
 * Enregistrement de l'autoloader de Composer
 * 
 * Un autoloader permet à PHP de charger les classes
 * qu'on utilise de manière automatique, sans qu'on
 * ait à inclure manuellement les fichiers qui les
 * définissent à chaque fois qu'on les utilise.
 * Pour ce faire, il est possible de fournir à PHP
 * une fonction qui aura pour responsabilité de
 * rechercher les définitions des classes à charger
 * et de les inclure.
 * Composer propose, entre autres, de définir pour
 * nous un autoloader à partir d'une configuration.
 * Ce projet fait générer son autoloader par
 * Composer avec une configuration suivant la
 * convention de structure PSR-4.
 * 
 * @see https://www.php-fig.org/psr/psr-4/
 * @see https://getcomposer.org/doc/04-schema.md#autoload
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Il est souvent utile de définir des constantes
 * de base dans un projet, notamment pour référencer
 * les chemins des répertoires principaux.
 * 
 * L'idée est de définir une seule référence à ces
 * chemins, la plupart du temps à partir d'un chemin
 * absolu, et d'utiliser cette référence plutôt
 * qu'une expression de chemin relative dans tout le
 * reste du projet.
 * Bien entendu, mieux vaut définir ces constantes
 * dans un fichier qui est très peu susceptible de
 * changer de répertoire...
 * 
 * @see https://www.php.net/manual/fr/language.constants.magic.php
 */
const CONFIG_DIR = __DIR__ . '/../config/';
