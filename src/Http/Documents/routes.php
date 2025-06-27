<?php

declare(strict_types=1);

use App\Http\Auth\AuthMiddleware;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TypeController;
use App\Http\Documents\Delete\DeleteDocumentController;
use App\Http\Documents\Delete\DeleteDocumentMiddleware;
use App\Http\Documents\DocumentController;
use App\Http\Documents\Download\DocumentValidationMiddleware;
use App\Http\Documents\Download\DownloadDocumentController;
use App\Http\Documents\Edit\EditDocumentController;
use App\Http\Documents\Preview\DocumentPreviewController;
use App\Http\Documents\Upload\UploadDocumentController;
use App\Http\Documents\Upload\UploadDocumentMiddleware;
use App\Http\Middleware\CheckSubscriptionMiddleware;
use Odan\Session\SessionInterface;
use Slim\Routing\RouteCollectorProxy;


/**
 * @var $app
 */
/* API */
$app->group('/api/documents', function (RouteCollectorProxy $group) use($app){

    $group->get('/directions', [DirectionController::class, 'directions']);
    $group->get('/direction', [DirectionController::class, 'direction']);

    $group->get('/sections', [SectionController::class, 'sections']);
    $group->get('/section', [SectionController::class, 'section']);

    $group->get('/types', [TypeController::class, 'types']);


    $group->get('/all', [DocumentController::class, 'all']);
    $group->get('/byDirection', [DocumentController::class, 'byDirection']);
    $group->get('/get', [DocumentController::class, 'get']);


    $group->post('/delete', [DeleteDocumentController::class, 'doDelete'])->add(DeleteDocumentMiddleware::class);
    $group->post('/edit', [EditDocumentController::class, 'doEdit']);
    $group->post('/upload', [UploadDocumentController::class, 'doUpload'])->add(UploadDocumentMiddleware::class);

    $group->post('/get-document', [DownloadDocumentController::class, 'getDocument'])
        ->add(CheckSubscriptionMiddleware::class)
        ->add(new AuthMiddleware(
            $app->getContainer()->get(SessionInterface::class),
            true
        ));

    $group->post('/preview', [DocumentPreviewController::class, 'doPreview'])
        ->add(new AuthMiddleware(
        $app->getContainer()->get(SessionInterface::class),
        true
    ));;

});

/* WEB */
$app->get('/documents/{slug}', [DocumentController::class, 'documents']);
$app->get('/document/{id}', [DocumentController::class, 'document']);
$app->get('/document/download/{token}', [DownloadDocumentController::class, 'download']);
