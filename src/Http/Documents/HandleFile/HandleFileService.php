<?php

namespace App\Http\Documents\HandleFile;

use App\Http\Documents\Document;
use InvalidArgumentException;
use RuntimeException;

class HandleFileService
{

    public function __construct(private readonly Document $document)
    {}


    public function insertFiles(array $files): int
    {
        if(empty($files)){
            throw new InvalidArgumentException('Files array cannot be empty');
        }
        $rows = $this->document->insertDocuments($files);
        if($rows === 0){
            throw new RuntimeException('Database insert failed');
        }
        return $rows;
    }
}
