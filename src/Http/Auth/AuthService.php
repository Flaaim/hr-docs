<?php

namespace App\Http\Auth;

use App\Http\Exception\Auth\InvalidCredentialsException;
use App\Http\Exception\Auth\TokenNotFoundException;
use App\Http\Exception\Auth\UserAlreadyExistsException;
use App\Http\Exception\Auth\UserNotFoundException;
use App\Http\Queue\Messages\Email\EmailResetMessage;
use App\Http\Queue\Messages\Email\EmailVerificationMessage;
use App\Http\Services\CookieManager;
use DateTimeImmutable;
use Odan\Session\SessionInterface;
use PHPUnit\Event\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\MessageBus;


class AuthService
{
    private Auth $userModel;
    private SessionInterface $session;
    private CookieManager $cookieManager;
    private MessageBus $messageBus;
    private LoggerInterface $logger;

    public function __construct(Auth $userModel, SessionInterface $session, CookieManager $cookieManager, MessageBus $messageBus, LoggerInterface $logger)
    {
        $this->userModel = $userModel;
        $this->session = $session;
        $this->cookieManager = $cookieManager;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function register(string $email, string $password): void
    {
        $user = $this->userModel->findByEmail($email);
        if (!empty($user)) {
            throw new UserAlreadyExistsException("Пользователь с таким email уже зарегистрирован.");
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $verifyToken = bin2hex(random_bytes(32));

        $created = $this->userModel->createUser($email, $password, $verifyToken);
        if(!$created){
            throw new RuntimeException('Ошибка при создании пользователя');
        }
        $this->messageBus->dispatch(new EmailVerificationMessage($email, $verifyToken));
    }

    public function login(string $email, string $password, bool $remember_me = false): void
    {
        $user = $this->userModel->findByEmail($email);
        if(empty($user)){
            throw new UserNotFoundException('Пользователь не найден');
        }
        if(!password_verify($password, $user['password_hash'])){
            throw new InvalidCredentialsException('Неверный email или пароль');
        }
        $this->session->set('user', [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'created_at' => $user['created_at'],
        ]);

        if($remember_me){
            $this->createRememberToken($user['id']);
        }
    }

    public function verifiedUser(?string $token): void
    {
        if(!$token){
            throw new InvalidArgumentException('Токен подтверждения не предоставлен.');
        }
        $confirmation = $this->userModel->findVerifyToken($token);
        if(empty($confirmation)){
            throw new TokenNotFoundException('Токен подтверждения не найден');
        }
        if (time() > $confirmation['expires']) {
            $this->userModel->deleteVerifyToken($token); // Удаляем просроченный токен
            throw new TokenNotFoundException('Срок действия токена истек');
        }
        $this->userModel->markUserAsVerified($confirmation['user_id']);
    }
    public function updatePassword(?string $token, string $password): void
    {
        if(!$token){
            throw new InvalidArgumentException('Токен подтверждения не предоставлен.');
        }
        $reset = $this->userModel->findResetToken($token);
        if(empty($reset)){
            throw new TokenNotFoundException('Токен сброса пароля не найден');
        }
        if (time() > $reset['expires']) {
            $this->userModel->deleteResetToken($token); // Удаляем просроченный токен
            throw new TokenNotFoundException('Срок действия токена истек');
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->updateUserPassword($reset['user_id'], $password);
    }
    public function reset($email): void
    {
        $user = $this->userModel->findByEmail($email);
        if(empty($user)){
            throw new UserNotFoundException("Пользователь не найден");
        }
        $token = bin2hex(random_bytes(32));
        $expires = (new DateTimeImmutable())->modify('+1 hour')->getTimestamp();
        $created = $this->userModel->createReset($user['id'], $token, $expires);
        if($created === 0){
            throw new RuntimeException('Ошибка сброса пароля');
        }
       $this->messageBus->dispatch(new EmailResetMessage($user['email'], 'Сброс пароля', $token));
    }

    public function logOut(): void
    {
        $this->session->remove('user');
        $token = $this->cookieManager->get('remember_token');
        if ($token !== null) {
            $this->userModel->deleteRememberToken($token);
            $this->cookieManager->delete('remember_token');
        }
    }
    public function createRememberToken(int $user_id): void
    {
        $token = bin2hex(random_bytes(32));
        $date = new DateTimeImmutable('+30 days');
        $this->userModel->saveRememberToken($user_id, $token, $date);
        $this->cookieManager->set(
            'remember_token',
            $token,
            $date->getTimestamp()
        );

    }

    public function registerOrLoginSocialUser(string $provider, string $socialId, string $email): array
    {
        /* Login by social ID */
        $user = $this->userModel->findBySocialId($provider, $socialId);
        if($user){
            return $user;
        }
        /* Login by Email and Merge Social account */
        $user = $this->userModel->findByEmail($email);
        if ($user) {
            // Привязываем социальный аккаунт к существующему
            $this->userModel->addSocialAccount($user['id'], $provider, $socialId);
            return $user;
        }
        /* Create new user by social acc */
        $user_id = $this->userModel->createUserBySocial($email);
        $this->userModel->addSocialAccount($user_id, $provider, $socialId);
        return $this->userModel->findById($user_id);
    }
    public function checkRememberMe(array $cookies): bool
    {
        if($this->session->has('user')){
            return true;
        }

        if(!isset($cookies['remember_token'])){
            return false;
        }

        $user = $this->userModel->findByToken($cookies['remember_token']);

        if(empty($user)){
            $this->cookieManager->delete('remember_token');
            $this->logger->warning('Invalid remember_token:', ['token' => $cookies['remember_token']]);
            throw new TokenNotFoundException('Токен пользователя не найден');
        }

        if(isset($user['expires_at']) && strtotime($user['expires_at']) < time()){
            $this->cookieManager->delete('remember_token');
            throw new TokenNotFoundException('Срок действия токена истек');
        }

        $this->session->set('user', [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'created_at' => $user['created_at'],
        ]);
        return true;
    }
}
