<?php

namespace App\Service;

use App\Exception\CannotOpenFileException;
use Exception;
use SplFileObject;
use Traversable;

class FileReader implements FileIteratorInterface
{
    private SplFileObject $fileObject;

    public function openFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new CannotOpenFileException(sprintf('File %s does not exist', $filePath));
        }

        try {
            $this->fileObject = new SplFileObject($filePath);
        } catch (Exception $e) {
            throw new CannotOpenFileException(
                sprintf( 'Could not open file due to exception. File: %s. Error: %s', $filePath, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    public function getIterator(): Traversable
    {
        return $this->fileObject;
    }
}