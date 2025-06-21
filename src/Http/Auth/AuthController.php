<?php

namespace App\Http\Auth;

use App\Http\Exception\Auth\InvalidCredentialsException;
use App\Http\Exception\Auth\TokenNotFoundException;
use App\Http\Exception\Auth\UserAlreadyExistsException;
use App\Http\Exception\Auth\UserNotFoundException;
use App\Http\Exception\Mail\MailNotSendException;
use App\Http\JsonResponse;
use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Framework\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LoggerInterface $logger
    ){}

    public function doLogin(Request $request, Response $response, array $args): Response
    {
        try{
            $data = $request->getParsedBody();

            $this->authService->login($data['email'], $data['password'], $data['remember_me'] ?? false);
            return new JsonResponse(['status' => 'success', 'message' => '🔓 Вы успешно вошли в систему!'], 200);
        }catch (InvalidCredentialsException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 401);
        }catch (UserNotFoundException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }catch (\Exception){
            return new JsonResponse([ 'status' => 'error' , 'errors' => 'Ошибка авторизации'], 500);
        }
    }

    public function doRegister(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        try {
            if ($data['password'] !== $data['confirm_password']) {
                throw new InvalidCredentialsException('Пароли не совпадают');
            }
            $this->authService->register($data['email'], $data['password']);
            return new JsonResponse(['status' => 'success', 'message' => 'Регистрация завершена. На ваш email направлено письмо с подтверждением.']);
        }
        catch (MailNotSendException $e){
            $this->logger->warning('Ошибка отправки почты', [$e->getMessage()]);
            return new JsonResponse(['status' => 'error', 'message' => 'Не удалось отправить письмо. Попробуйте позже или обратитесь в поддержку.'], 500);
        }catch (RuntimeException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }catch (InvalidCredentialsException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (UserAlreadyExistsException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }


    }
    public function doLogout(Request $request, Response $response, array $args): Response
    {
        try{
            $this->authService->logOut();
            return new JsonResponse(['status' => 'success', 'message' => 'Вы успешно вышли из профиля'], 200);
        }catch (\Exception $e){
            new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }


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
        }catch (TokenNotFoundException $e){
            throw new Exception($e->getMessage(), 401);
        } catch (InvalidArgumentException $e){
            return $view->render($response, 'pages/auth/verify.twig', [
                'title' => 'Подтверждение регистрации',
                'message' => $e->getMessage(),
            ]);
        }catch (\Exception $e){
            $this->logger->warning('Ошибка подтверждения пользователя', [
                'error' => $e->getMessage()
            ]);
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
