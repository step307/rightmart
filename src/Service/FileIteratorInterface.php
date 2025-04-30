<?php

namespace App\Service;

use App\Exception\CannotOpenFileException;
use IteratorAggregate;

interface FileIteratorInterface extends IteratorAggregate
{
    /**
     * @throws CannotOpenFileException
     */
    public function openFile(string $filePath): void;
}