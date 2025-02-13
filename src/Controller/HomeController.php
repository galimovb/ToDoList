<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(LoggerInterface $logger): Response
    {
        $logger->info('Главная страница загружена', [
            'user' => $this->getUser() ? $this->getUser()->getUserIdentifier() : 'Гость',
        ]);

        return $this->render('home/index.html.twig', [
            'vue_component' => 'home-page',
        ]);
    }
    #[Route('/user', name: 'user', methods: ['GET'])]
    public function getUserLogin(LoggerInterface $logger): JsonResponse
    {
        $sessionUser = $this->getUser();

        if (!$sessionUser) {
            $logger->warning('Попытка получения login неавторизованным пользователем.');
            return new JsonResponse(['error' => 'Пользователь не авторизован'], Response::HTTP_UNAUTHORIZED);
        }

        $logger->info('Вернули login и роли пользователя', [
            'user' => $sessionUser->getUserIdentifier(),
            'roles' => $sessionUser->getRoles(),
        ]);

        return new JsonResponse([
            'login' => $sessionUser->getUserIdentifier(),
            'roles' => $sessionUser->getRoles(),
        ], Response::HTTP_OK);
    }


}