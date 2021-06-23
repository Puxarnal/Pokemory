<?php

namespace Pokemory\Manager;

interface ManagerInterface
{
    public static function save($model): bool;

    public static function delete($model): bool;
}