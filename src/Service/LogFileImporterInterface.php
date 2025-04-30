<?php

namespace App\Service;

interface LogFileImporterInterface
{
    public function importFile(string $filePath): void;
}