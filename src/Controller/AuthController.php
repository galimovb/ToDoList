<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/login', name: 'login', methods: ['GET'])]
    public function index(): Response
    {
        $this->logger->info('Страница входа загружена');
        return $this->render('login/index.html.twig', [
            'vue_component' => 'login-page', // Имя Vue-компонента
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        $this->logger->info('Пользователь вышел из системы');
    }

    #[Route('/login/check', name: 'login_check', methods: ['POST'])]
    public function checkLogin(Request $request): JsonResponse
    {
        $this->logger->info('Проверка логина начата', ['request' => $request->request->all()]);

        $user = $this->getUser();
        if ($user === null) {
            $this->logger->error('Ошибка входа: пользователь не найден');
            return new JsonResponse(['message' => 'Ошибочка вышла'], 500);
        }

        $this->logger->info('Успешный вход', ['user' => $user->getUserIdentifier()]);

        return new JsonResponse([
            'message' => 'Успешно',
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ]
        ], 200);

    }
}