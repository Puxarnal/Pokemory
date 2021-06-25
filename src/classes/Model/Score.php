<?php

namespace Pokemory\Model;

use DateInterval;

/**
 * Représente le score d'un joueur
 */
class Score
{
    /**
     * @property int $id L'identifiant du score en base de données
     */
    protected $id;

    /**
     * @property DateInterval $time Le temps du joueur
     */
    protected $time;

    /**
     * Retourne l'identifiant du score
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le temps du joueur
     * @return DateInterval|null
     */
    public function getTime(): ?DateInterval
    {
        /*
         * ATTENTION!
         * Ici, on renvoie une copie de l'objet DateInterval
         * référencé par la proriété $this->time.
         * Si on renvoyait l'objet lui-même, alors il pourrait
         * être directement modifié depuis l'extérieur de la classe
         * et on perdrait tout l'intérêt de l'encapsulation
         * (c'est-à-dire que notre classe ne serait plus une
         * "boîte noire" puisque n'importe quel code pourrait
         * modifier ce qu'elle seule est censée pouvoir modifier).
         */
        return clone $this->time;
    }

    /**
     * Renseigne le temps du joueur
     * @param DateInterval  $time
     * @return static L'objet lui-même pour chaîner les appels de méthodes
     */
    public function setTime(DateInterval $time): self
    {
        /*
         * ATTENTION!
         * Comme dans le getter ci-dessus, on renseigne ici dans
         * la propriété $this->time une copie de l'objet qui a
         * été passé en paramètre de la méthode.
         * Ceci permet d'assurer l'encapsulation de cette propriété
         * car l'objet passé à $this->setTime() reste manipulable
         * par un code extérieur à la classe ; en d'autres termes,
         * si on enregistrait directement cet objet comme propriété,
         * celle-ci pourrait être directement modifiée par du code
         * étranger à la classe.
         */
        $this->time = clone $time;

        return $this;
    }
}
