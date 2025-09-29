<?php

namespace App\Http\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AdminController
{
    public function index(Request $request, Response $response, array $args): Response
    {

        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/admin/dashboard.twig', [
            'title' => 'Админка',
        ]);
    }

    public function users(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/admin/users.twig');
    }

    public function payments(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/admin/payments.twig');
    }

    public function check(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/admin/check.twig');
    }

    public function mailing(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/admin/mailing.twig');
    }

    public function logs(Request $request, Response $response, array $args): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/admin/logs.twig');
    }
}
