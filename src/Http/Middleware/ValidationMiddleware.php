<?php

namespace App\Http\Middleware;

use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use Slim\Psr7\Factory\ResponseFactory;

class ValidationMiddleware implements MiddlewareInterface
{
    private array $rules;
    private array $messages;

    public function __construct(array $rules, array $messages = [])
    {
        $this->rules = $rules;
        $this->messages = $messages;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody() ?? [];
        try {
            foreach ($this->rules as $field => $rule) {
                if ($rule instanceof Validator) {
                    $rule->setName($field)->assert($data[$field]);
                }
            }
        } catch (NestedValidationException $e) {
            $errors = $e->getMessages();
            if (!empty($this->messages)) {
                $errors = $this->mapCustomMessages($errors);
            }
            return new JsonResponse([
                'status' => 'error',
                'errors' => $errors
            ], 422);
        }
        return $handler->handle($request);
    }

    private function mapCustomMessages(array $errors): array
    {
        $result = [];
        foreach ($errors as $field => $message) {
            $result[$field] = $this->messages[$field] ?? $message;
        }
        return $result;
    }
}
