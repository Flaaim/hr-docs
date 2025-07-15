<?php

namespace App\Http\Documents;

use App\Http\Exception\Document\DirectionNotFoundException;
use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\JsonResponse;
use App\Http\Models\Direction;
use App\Http\Models\Section;
use App\Http\Models\Type;
use App\Http\Paginator;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class DocumentController
{
    private Section $section;
    private Type $type;
    private Document $document;
    private Direction $direction;
    private DocumentService $service;

    public function __construct(Direction $direction, Section $section, Type $type, Document $document, DocumentService $service)
    {
        $this->section = $section;
        $this->type = $type;
        $this->document = $document;
        $this->direction = $direction;
        $this->service = $service;
    }


    public function all(Request $request, Response $response, array $args): Response
    {
        try{
            $documents = $this->document->getAll();
            return new JsonResponse($documents, 200);
        }catch(\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function get(Request $request, Response $response, array $args): Response
    {
        try{
            $document_id = $request->getQueryParams()['document_id'] ?? null;
            if($document_id === null){
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Document ID is required'
                ], 400);
            }
            $document = $this->document->getById($document_id);
            return new JsonResponse($document, 200);
        }catch(\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function byDirection(Request $request, Response $response, array $args): Response
    {
        try{
            $direction_id = $request->getQueryParams()['direction_id'];
            $documents = $this->document->getByDirection($direction_id);
            return new JsonResponse($documents);
        }catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function document(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        try{
            $document = $this->document->getById($id);
            if(empty($document)){
                throw new DocumentNotFoundException();
            }
            $schema = $this->service->getDocumentSchema($document);
            $documents = $this->document->getAll([], 6);
            return Twig::fromRequest($request)->render($response, 'pages/documents/document.twig', [
                'document' => $document,
                'documents' => $documents,
                'schema' => $schema
            ]);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), 500);
        }
        catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, 'Server error');
        }
    }
    public function documents(Request $request, Response $response, array $args): Response
    {
        $direction_slug = $args['slug'] ?? '';
        try {
            $page = $request->getQueryParams()['page'] ?? 1;
            $itemsPerPage = 25;
            $count = $this->service->getCountByDirectionSlug($direction_slug);
            $paginator = new Paginator($page, $count, $itemsPerPage);

            $result = $this->service->getDocumentsByDirectionSlug(
                $direction_slug,
                $paginator->getItemsPerPage(),
                $paginator->getOffset()
            );

            return Twig::fromRequest($request)->render(
                $response,
                'pages/documents/documents.twig',
                [
                    'documents' => $result['documents'],
                    'direction' => $result['direction'],
                    'paginator' => $paginator
                ]
            );
        }catch (DirectionNotFoundException $e){
            throw new HttpNotFoundException($request, $e->getMessage());
        }catch (InvalidArgumentException $e) {
            throw new HttpBadRequestException($request, $e->getMessage());
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, 'Server error');
        }
    }

    public function findOrphanedFiles(Request $request, Response $response, array $args): Response
    {
        try {
            $orphanedFiles = $this->service->findOrphanedFiles();
            return new JsonResponse($orphanedFiles, 200);
        } catch (DocumentNotFoundException|DirectoryNotFoundException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function findLostFiles(Request $request, Response $response, array $args): Response
    {
        $lostFilesNames = $this->service->findLostFilesNames();
        $documents = $this->document->getByInValues('stored_name', $lostFilesNames);
        return new JsonResponse($documents, 200);
    }



}
