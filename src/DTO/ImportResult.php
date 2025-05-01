<?php

namespace App\DTO;

class ImportResult
{
    public int $linesRead = 0;
    public int $emptyLinesSkipped = 0;
    public int $parseErrors = 0;
    public int $saveErrors = 0;
    public int $savedSuccessfully = 0;
}