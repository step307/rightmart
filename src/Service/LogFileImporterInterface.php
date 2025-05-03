<?php

namespace App\Service;

use App\DTO\ImportResult;
use App\Exception\CannotOpenFileException;

interface LogFileImporterInterface
{
    /**
     * @throws CannotOpenFileException
     */
    public function importFile(string $filePath): ImportResult;
}