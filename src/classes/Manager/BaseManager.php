<?php

namespace Pokemory\Manager;

use PDO;
use ReflectionProperty;

abstract class BaseManager implements ManagerInterface
{
    private static $connection;

    protected static function getConnection()
    {
        if (!self::$connection) {
            try {
                $config = require CONFIG_DIR . 'database.php';

                self::$connection = new PDO(
                    "{$config['driver']}:host={$config['host']};dbname={$config['database']}",
                    $config['user'],
                    $config['password']
                );

            } catch (\Exception $e) {
                // TODO: handle errors
                return null;
            }
        }

        return self::$connection;
    }

    protected static function setModelProperties(object $model, array $props): void
    {
        foreach ($props as $name => $value) {
            $reflect = new ReflectionProperty($model, $name);
            $reflect->setAccessible(true);
            $reflect->setValue($model, $value);
        }
    }
}