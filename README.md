# Pokémory

Ce mini-projet est un jeu de mémoire dans lequel vous retrouverez Pikachu et ses 150 premiers copains !

## Installation

Vous devrez installer les packages Node pour pouvoir construire les builds CSS et JS :

```
 npm install
```

Vous devrez également générer l'autoloader PHP avec Composer :

```
composer dump-autoload
```

## Build

Le build des ressources statiques pour le front se fait exclusivement via des scripts NPM.

> npm run build

Il est également possible d'effectuer les builds séparément.

### Build CSS

```
npm run build-css
```

### Build JS

```
npm run build-js
```

## Lancement avec Docker

Une stack est proposée pour un lancement rapide du projet avec Docker-compose.

Cette stack comprend :

- un serveur Nginx ([`nginx:latest`](https://hub.docker.com/_/nginx))
- un interpréteur PHP 7 ([`php:7-fpm`](https://hub.docker.com/_/php))
- un serveur MariaDB ([`mariadb:latest`](https://hub.docker.com/_/mariadb))
- un hébergement de phpMyAdmin ([`phpmyadmin:latest`](https://hub.docker.com/_/phpmyadmin))

Les fichiers de configuration des containers, notamment un dump initial de la base de données et la configuration du serveur web, se trouvent dans le répertoire `.docker`.

```
docker-compose up -d
```

### Attention
Il est possible qu'au premier démarrage la base de donnée ne soit pas accessible tout de suite. ceci est dû au fait que le container `mariadb` est considéré comme opérationnel avant d'avoir terminé la première initialisation de la base à partir du dump.

Dans ce cas, quelques secondes de patience devraint faire l'affaire.

-----

## Notes

### Structure

La structure des répertoires est adaptée de la proposition [`pds/skeleton`](https://github.com/php-pds/skeleton) pour un template de projet PHP standard. Le projet étant destiné à une utilisation pédagogique, j'ai préféré ne pas opter pour l'utilisation d'une architecture imposée par une technologie, framework ou library.

### Parti pris

En l'absence de support de cours et de programme d'enseignement précis que ce projet devrait accompagner, j'ai opté pour une réalisation commentée en détail, à la manière d'une (longue) explication de code.

Le but derrière ce parti pris est de proposer aux étudiants susceptibles de vouloir se plonger dans le code source de ce mini-jeu une lecture enrichie.

-----

## Par où commencer ?

Par le commencement, voyons !

### PHP

Le point d'entrée de l'application est [`public/index.php`](https://github.com/Puxarnal/Pokemory/blob/master/public/index.php).
Il est possible de naviguer vers presque tout le reste du code PHP depuis ce script.

### SCSS

Le fichier SCSS principal est [`resources/scss/main.scss`](https://github.com/Puxarnal/Pokemory/blob/master/resources/scss/main.scss).

### JS

Le seul fichier JavaScript du projet est [`resources/js/game.js`](https://github.com/Puxarnal/Pokemory/blob/master/resources/js/game.js).
