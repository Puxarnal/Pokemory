<?php

namespace Pokemory;

use DateInterval;
use DateTime;

abstract class Utils
{
    public static function secondsToInterval(int $seconds): DateInterval
    {
        return (new DateTime())->diff(new DateTime("now $seconds seconds"));
    }

    public static function intervalToSeconds(DateInterval $interval): int
    {
        $now = new DateTime();

        $date = clone $now;
        if ($interval->invert) {
            $date->sub($interval);
        } else {
            $date->add($interval);
        }

        return $now->getTimestamp() - $date->getTimestamp();
    }
}