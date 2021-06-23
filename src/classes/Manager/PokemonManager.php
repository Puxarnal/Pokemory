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
        $connection = static::getConnection();

        $data = [
            'name' => $pokemon->getName(),
            'fr' => $pokemon->getDisplayName(),
            'img' => $pokemon->getImageUrl()
        ];

        if ($pokemon->id) {
            $query = $connection->prepare(
                'UPDATE `pokemons` SET `name` = :name, `fr` = :fr, `img` = :img WHERE `id` = :id'
            );

            return $query->execute($data);
        }

        $query = $connection->prepare(
            'INSERT INTO `pokemons` (`name`, `fr`, `img`) VALUES (:name, :fr, :img)'
        );

        if ($query->execute($data)) {
            static::setModelProperties($pokemon, ['id' => $connection->lastInsertId()]);
            return true;
        }

        return false;
    }

    public static function delete($pokemon): bool
    {
        if (!$pokemon->id) {
            return false;
        }

        $connection = static::getConnection();

        $query = $connection->prepare(
            'DELETE FROM `pokemons` WHERE `id` = :id'
        );

        return $query->execute(['id' => $pokemon->id]);
    }

    public static function findRandom(int $limit)
    {
        $connection = static::getConnection();

        $query = $connection->prepare(
            'SELECT `id`, `name`, `fr`, `img` FROM `pokemons` ORDER BY RAND() LIMIT :limit'
        );

        $query->bindParam(':limit', $limit, \PDO::PARAM_INT);

        if (!$query->execute()) {
            return [];
        }

        $pokemons = [];
        while ($row = $query->fetch()) {
            $pokemon = new Pokemon();
            static::setModelProperties($pokemon, [
                'id' => $row['id'],
                'name' => $row['name'],
                'fr' => $row['fr'],
                'img' => $row['img']
            ]);

            $pokemons[] = $pokemon;
        }

        return $pokemons;
    }
}