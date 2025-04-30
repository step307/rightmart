<?php

namespace App\Repository;

use App\DTO\HttpLogLine;

interface HttpLogRepositoryInterface
{
    public function save(HttpLogLine $httpLogLine): void;
}