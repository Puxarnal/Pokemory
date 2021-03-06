<?php

namespace Pokemory\Manager;

use Pokemory\Model\Score;
use Pokemory\Utils;

class ScoreManager extends BaseManager
{
    /**
     * Enregistre le temps d'un joueur en base de données
     * @param Score $score
     * @return bool
     */
    public static function save($score): bool
    {
        $connection = static::getConnection();

        $data = [
            'time' => Utils::intervalToSeconds($score->getTime())
        ];

        if ($score->getId()) {
            $query = $connection->prepare(
                'UPDATE `scores` SET = :time WHERE `id` = :id'
            );

            $query->bindValue('id', $score->getId());

            return $query->execute($data);
        }
        
        $query = $connection->prepare(
            'INSERT INTO `scores` (`time`) VALUES (:time)'
        );

        if ($query->execute($data)) {
            static::setModelProperties($score, ['id' => $connection->lastInsertId()]);
            return true;
        }

        return false;
    }

    /**
     * Supprime le temps d'un joueur de la base de données
     * @param Score $score
     * @return bool
     */
    public static function delete($score): bool
    {
        if (!$score->getId()) {
            return false;
        }

        $connection = static::getConnection();

        $query = $connection->prepare(
            'DELETE FROM `scores` WHERE `id` = :id'
        );

        return $query->execute(['id' => $score->getId()]);
    }

    /**
     * Récupère une liste des meilleurs scores enregistrés,
     * triés avec le meilleur en premier
     * @param int $limit Le nombre de temps à récupérer
     * @return Score[]
     */
    public static function findBestScores(int $limit): array
    {
        $connection = static::getConnection();

        if (!$connection) {
            return [];
        }

        $query = $connection->prepare(
            'SELECT `id`, `time` FROM `scores` ORDER BY `time` ASC LIMIT :limit'
        );

        $query->bindParam('limit', $limit, \PDO::PARAM_INT);

        if (!$query->execute()) {
            return [];
        }

        $scores = [];
        while ($row = $query->fetch()) {
            $score = new Score();
            static::setModelProperties($score, [
                'id' => $row['id'],
                'time' => Utils::secondsToInterval($row['time'])
            ]);

            $scores[] = $score;
        }

        return $scores;
    }
}
