<?php

namespace App\Http\Documents\Upload;

use App\Http\Documents\FileSystemService;
use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class UploadDocumentMiddleware implements MiddlewareInterface
{
    private Filename $filename;
    private FileSystemService $fileSystemService;
    public function __construct(Filename $filename, FileSystemService $fileSystemService)
    {
        $this->filename = $filename;
        $this->fileSystemService = $fileSystemService;
    }
    public function process(Request $request, Handler $handler): ResponseInterface
    {
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedData = [];

        $files = $uploadedFiles['file'] ?? null;

        if($files === NULL){
            return $this->createErrorResponse(404, 'File not found');
        }

        if(!empty($files)){
            foreach ($files as $file) {
                switch ($file->getError()){
                    case UPLOAD_ERR_OK:
                        try {
                            $originalFilename = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
                            $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

                            $filename = $this->generateFilename($originalFilename);
                            $uploadDir = $this->fileSystemService->generateUploadDir($filename, $extension);

                            if($this->fileSystemService->fileExists($uploadDir)){
                                unlink($uploadDir);
                            }
                            $file->moveTo($uploadDir);

                            $uploadedData[] = [
                                'title' => $originalFilename,
                                'mime_type' => $extension,
                                'stored_name' => $filename,
                                'section_id' => $request->getParsedBody()['sections'],
                                'type_id' => $request->getParsedBody()['types'],
                                'size' => $file->getSize(),
                                'updated' => (new \DateTimeImmutable())->getTimestamp()
                            ];
                        }catch (\Exception $e) {
                            return $this->createErrorResponse(500, $e->getMessage());
                        }
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        return $this->createErrorResponse(413, 'File size too large');
                    case UPLOAD_ERR_PARTIAL:
                        return $this->createErrorResponse(400, 'File only partially uploaded');

                    case UPLOAD_ERR_NO_FILE:
                        return $this->createErrorResponse(400, 'No file was uploaded');

                    case UPLOAD_ERR_NO_TMP_DIR:
                        return $this->createErrorResponse(500, 'Missing temporary folder');

                    case UPLOAD_ERR_CANT_WRITE:
                        return $this->createErrorResponse(500, 'Failed to write file to disk');

                    case UPLOAD_ERR_EXTENSION:
                        return $this->createErrorResponse(500, 'File upload stopped by extension');

                    default:
                        return $this->createErrorResponse(500, 'Unknown upload error');

                }
            }

        }

        if(!empty($uploadedData)){
            $request = $request->withAttribute('file_data', $uploadedData);
        }

        return $handler->handle($request);
    }

    private function createErrorResponse(int $code, string $message): ResponseInterface
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    private function generateFilename(string $originalFilename): string
    {
        return $this->filename->filter_filename(strtolower($this->filename->transliterate($originalFilename)));
    }

    private function generateUploadDir(string $filename, string $extension): string
    {
        return dirname(__DIR__, 4) . '/public/uploads/' . $filename.'.'.$extension;
    }
}
