<?php

namespace App\Http\Middleware;

use Respect\Validation\Validator;

class ValidationMiddlewareFactory
{
    public static function create(array $rules, array $messages = []): ValidationMiddleware
    {
        $preparedRules = [];
        foreach ($rules as $field => $rule) {
            $preparedRules[$field] = $rule;
        }
        return new ValidationMiddleware($preparedRules, $messages);
    }
}
