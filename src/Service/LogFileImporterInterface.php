<?php

namespace App\Service;

use App\DTO\ImportResult;

interface LogFileImporterInterface
{
    public function importFile(string $filePath): ImportResult;
}