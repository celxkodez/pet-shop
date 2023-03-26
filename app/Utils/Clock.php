<?php

namespace App\Utils;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class Clock implements ClockInterface
{

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
