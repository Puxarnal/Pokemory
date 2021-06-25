<?php

namespace Pokemory;

use DateInterval;
use DateTime;

/**
 * Regroupe des fonctions-outils
 * @abstract
 */
abstract class Utils
{
    /**
     * Convertit une durée un objet DateInterval
     * @param int $seconds La durée en secondes
     * @return DateInterval
     * 
     * @see https://www.php.net/manual/fr/class.datetime.php
     * @see https://www.php.net/manual/fr/class.dateinterval.php
     * @see https://www.php.net/manual/fr/datetime.diff.php
     */
    public static function secondsToInterval(int $seconds): DateInterval
    {
        // On prend comme point de référence le moment présent
        $now = new DateTime();
        $off = clone $now;

        // On se positionne au moment présent décalé de la durée donnée
        $off->setTimestamp($off->getTimestamp() + $seconds);

        // On retourne la différence sous la forme d'un objet DateInterval
        return $now->diff($off);
    }

    /**
     * Convertit un objet DateInterval en une durée exprimée en secondes
     * @param DateInterval $interval La durée sous la forme d'un objet DateInterval
     * @return int
     * 
     * @see https://www.php.net/manual/fr/class.dateinterval.php#dateinterval.props.invert
     * @see https://www.php.net/manual/fr/datetime.sub.php
     * @see https://www.php.net/manual/fr/datetime.add.php
     */
    public static function intervalToSeconds(DateInterval $interval): int
    {
        // On prend comme point de référence le moment présent
        $now = new DateTime();
        $off = clone $now;

        // Si l'intervalle est négatif...
        if ($interval->invert) {
            // ...alors on calcule un moment dans le passé
            $off->sub($interval);
        } else {
            // ...sinon on calcule un moment dans le futur
            $off->add($interval);
        }

        /**
         * On retourne la différence du timestamp du moment calculé
         * et de la référence.
         */
        return $off->getTimestamp() - $now->getTimestamp();
    }
}
