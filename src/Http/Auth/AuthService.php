<?php

namespace App\Http\Auth;

use App\Http\Exception\UserAlreadyExistsException;
use App\Http\Exception\UserNotFoundException;
use App\Http\Services\Mail\Mail;
use DateTimeImmutable;
use InvalidArgumentException;
use Odan\Session\SessionInterface;
use RuntimeException;

class AuthService
{
    private Auth $userModel;
    private Mail $mailer;
    private SessionInterface $session;

    public function __construct(Auth $userModel, Mail $mailer, SessionInterface $session)
    {
        $this->userModel = $userModel;
        $this->mailer = $mailer;
        $this->session = $session;
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

        $this->mailer->setTo($email)
            ->setSubject('Регистрация на сайте')
            ->setBodyFromTemplate(
                'emails/welcome.html.twig',
                ['email' => $email, 'link' => 'https://hr-docs.ru/auth/verify?token='.$verifyToken]
            )
            ->send();
    }

    public function login(string $email, string $password): void
    {
        $user = $this->userModel->findByEmail($email);
        if(empty($user)){
            throw new UserNotFoundException('Пользователь не найден');
        }
        if(!password_verify($password, $user['password_hash'])){
            throw new InvalidArgumentException('Неверный пароль');
        }
        $this->session->set('user', [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'created_at' => $user['created_at'],
        ]);
    }

    public function verifiedUser(?string $token): void
    {
        if(!$token){
            throw new RuntimeException('Токен подтверждения не предоставлен.');
        }
        $confirmation = $this->userModel->findVerifyToken($token);
        if(!$confirmation){
            throw new RuntimeException('Токен подтверждения не найден');
        }
        $this->userModel->markUserAsVerified($confirmation['user_id']);
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
        if(!$created){
            throw new RuntimeException('Ошибка сброса пароля');
        }

        $this->mailer->setTo($email)
            ->setSubject('Сброс пароля')
            ->setBodyFromTemplate(
                'emails/reset.twig',
                ['link' => 'https://hr-docs.ru/auth/reset?token='.$token]
            )
            ->send();
    }
}
