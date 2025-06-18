<?php

namespace App\Http\Auth;

use App\Http\Exception\UserAlreadyExistsException;
use App\Http\Exception\UserNotFoundException;
use App\Http\Interface\MailInterface;
use App\Http\JsonResponse;
use App\Http\Services\Mail\Mail;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\Views\Twig;

class AuthController
{
    private Auth $userModel;
    private SessionInterface $session;
    private MailInterface $mail;
    private AuthService $authService;

    public function __construct(Auth $userModel, SessionInterface $session, Mail $mail)
    {
        $this->userModel = $userModel;
        $this->session = $session;
        $this->mail = $mail;
        $this->authService = new AuthService($this->userModel, $this->mail);

    }

    public function doLogin(Request $request, Response $response, array $args): Response
    {
        try{
            $email = $request->getParsedBody()['email'];
            $password = $request->getParsedBody()['password'];
            if($user = $this->authService->login($email, $password)){
                $this->session->set('user', [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'created_at' => $user['created_at'],
                ]);
                return new JsonResponse(['status' => 'success', 'message' => '🔓 Вы успешно вошли в систему!'], 200);
            }
            return new JsonResponse(['status' => 'error', 'message' => 'Ошибка авторизации']);
        }catch (UserNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\Exception){
            return new JsonResponse([ 'status' => 'error' , 'errors' => 'Неверный email или пароль'], 401);
        }
    }

    public function doRegister(Request $request, Response $response, array $args): Response
    {
        $email = $request->getParsedBody()['email'];
        $password = $request->getParsedBody()['password'];
        try {
            $this->authService->register($email, $password);
            return new JsonResponse(['status' => 'success', 'message' => 'Регистрация завершена. На ваш email направлено письмо с подтверждением.']);
        } catch (UserAlreadyExistsException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }


    }
    public function doLogout(Request $request, Response $response, array $args): Response
    {
        $this->session->remove('user');
        return new JsonResponse(['status' => 'success', 'message' => 'Вы успешно вышли из профиля'], 200);
    }


    public function doVerify(Request $request, Response $response, array $args): Response
    {
        $token = $request->getQueryParams()['token'] ?? null;
        $view = Twig::fromRequest($request);
        try{
            $this->authService->verifiedUser($token);
            return $view->render($response, 'pages/auth/verify.twig', [
                'title' => 'Подтверждение регистрации',
                'message' => 'Ваш email успешно подтверждён! Теперь вы можете войти.',
            ]);
        }catch (RuntimeException $e){
            return $view->render($response, 'pages/auth/verify.twig', [
                'title' => 'Подтверждение регистрации',
                'message' => $e->getMessage(),
            ]);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

    }

    public function doReset(Request $request, Response $response, array $args): Response
    {
        $email = $request->getParsedBody()['email'];
        try{
            $this->authService->reset($email);
            return new JsonResponse(['status' => 'success', 'message' => 'На ваш email направлено письмо с инструкциями по сбросу пароля.']);
        }catch (UserNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => 'Ошибка сброса пароля']);
        }
    }
}
