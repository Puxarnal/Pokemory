<?php

namespace Pokemory\Manager;

/**
 * Définit des méthodes que doivent implémenter les managers
 * @see https://www.php.net/manual/fr/language.oop5.interfaces.php
 */
interface ManagerInterface
{
    /**
     * Enregistre un modèle en base de données
     * @param object $model
     * @return bool
     */
    public static function save($model): bool;

    /**
     * Supprime un modèle de la base de données
     * @param object $model
     * @return bool
     */
    public static function delete($model): bool;
}
