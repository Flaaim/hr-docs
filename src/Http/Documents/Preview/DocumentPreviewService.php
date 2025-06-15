<?php

namespace App\Http\Documents\Preview;

use App\Http\Documents\Document;
use App\Http\Documents\Download\DownloadDocumentService;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\DocumentNotFoundException;
use App\Http\Exception\DocumentWrongTypeException;
use PhpOffice\PhpWord\IOFactory;

class DocumentPreviewService
{
    private FileSystemService $fileSystem;
    private Document $document;
    private DownloadDocumentService $downloadService;
    public function __construct(Document $document, FileSystemService $fileSystemService, DownloadDocumentService $downloadService)
    {
        $this->document = $document;
        $this->fileSystem = $fileSystemService;
        $this->downloadService = $downloadService;
    }

    public function previewDocument(int $document_id): string
    {
        $document = $this->downloadService->getDocument($document_id);
        if(empty($document)){
            throw new DocumentNotFoundException('Document not found');
        }
        if(strtolower($document['mime_type']) === 'doc'){
            throw new DocumentWrongTypeException('Предварительный просмотр файла неподдерживается');
        }
        $filePath = $this->fileSystem->generateUploadDir(
            $document['stored_name'],
            $document['mime_type']
        );
        $phpWord = IOFactory::load($filePath);
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $htmlWriter->save('php://output');
        $htmlContent  = ob_get_clean();
        return <<<HTML
            <script>
                const container = document.querySelector('#previewContainer');
                container.addEventListener("contextmenu", e => e.preventDefault());
                container.addEventListener("copy", e => e.preventDefault());
            </script>
            $htmlContent
        HTML;
    }
}
