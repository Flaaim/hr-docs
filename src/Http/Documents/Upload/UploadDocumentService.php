<?php

namespace App\Http\Documents\Upload;

use App\Http\Documents\Document;

class UploadDocumentService
{
    private Document $document;

    public function __construct(Document $document)
    {

        $this->document = $document;
    }


    public function insertFiles(array $files): int
    {
        if(empty($files)){
            throw new \InvalidArgumentException('Files array cannot be empty', 400);
        }
        $rows = $this->document->insertDocuments($files);
        if($rows === 0){
            throw new \RuntimeException('Database insert failed', 500);
        }
        return $rows;
    }
}
