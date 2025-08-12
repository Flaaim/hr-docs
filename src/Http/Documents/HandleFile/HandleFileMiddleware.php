<?php

namespace App\Http\Documents\HandleFile;

use App\Http\Documents\FileSystemService;
use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HandleFileMiddleware implements MiddlewareInterface
{
    const ACTION_UPLOAD = 'upload';
    const ACTION_RELOAD = 'reload';

    public function __construct(
        private readonly FileNameHelper $fileNameHelper,
        private readonly HandleFileData $handleFileData,
        private readonly FileSystemService $fileSystemService,
        private readonly MimeTypeMapper $mimeTypeMapper,
    )
    {}
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $action = $request->getParsedBody()['action'] ?? null;
        $files = $request->getUploadedFiles()['file'] ?? null;

        if ($action === null) {
            return $this->createErrorResponse(400, 'Action is required.');
        }

        if ($files === null) {
            return $this->createErrorResponse(400, 'File not found');
        }

        if(!empty($files)){
            if(!$this->mimeTypeMapper->checkUploadedFiles($files)){
                return $this->createErrorResponse(400, 'File types not supports');
            };
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
                            $uploadedData[] = match ($action) {
                                self::ACTION_UPLOAD =>  (clone $this->handleFileData)
                                    ->setTitle($originalFilename)
                                    ->setStoredName($filename)
                                    ->setMimeType($extension)
                                    ->setSectionId($request->getParsedBody()['sections'])
                                    ->setTypeId($request->getParsedBody()['types'])
                                    ->setSize($file->getSize())
                                    ->setUpdated(),
                                self::ACTION_RELOAD => $this->handleFileData
                                    ->setStoredName($filename)
                                    ->setMimeType($extension)
                                    ->setSize($file->getSize())
                                    ->setUpdated(),
                                default =>  $this->createErrorResponse(400, "Unknown action '$action'")
                            };
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
        return $this->fileNameHelper->filter_filename(strtolower($this->fileNameHelper->transliterate($originalFilename)));
    }

    private function generateUploadDir(string $filename, string $extension): string
    {
        return dirname(__DIR__, 4) . '/public/uploads/' . $filename.'.'.$extension;
    }
}
