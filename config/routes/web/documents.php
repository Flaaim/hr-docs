<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Documents\DocumentController;
use App\Http\Documents\Download\DownloadDocumentController;

$app->get('/documents/{slug}', [DocumentController::class, 'documents']);

$app->get('/document/{id}', [DocumentController::class, 'document']);
$app->get('/document/download/{token}', [DownloadDocumentController::class, 'download']);



