<?php

namespace App\Controller;

use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrController extends AbstractController
{
    private UserService $userService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(UserService $userService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->userService = $userService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/registr', name: 'registr', methods: ['GET'])]
    public function index(): Response
    {
        $this->logger->info('Пользователь на странице регистрации');

        return $this->render('registr/index.html.twig', [
            'vue_component' => 'registr-page',
        ]);
    }

    #[Route('/registr/new', name: 'registr_new', methods: ['POST'])]
    public function saveNewUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $login = $data['login'] ?? null;
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;

        $this->logger->info('Попытка регистрации нового пользователя', [
            'login' => $login,
            'time' => date('Y-m-d H:i:s')
        ]);

        if ($this->userService->checkUserExistsByLogin($login)) {
            $this->logger->warning('Такой пользователь уже существует, регистрация прервалась', [
                'login' => $login,
                'time' => date('Y-m-d H:i:s')
            ]);
            return new JsonResponse(['message' => 'Такой пользователь уже существует'], 400);
        }

        $this->userService->createNewUser($login, $password, $firstName, $lastName);

        $this->logger->info('Пользователь успешно зарегистрировался', [
            'login' => $login,
            'time' => date('Y-m-d H:i:s')
        ]);

        return new JsonResponse(['message' => 'Пользователь успешно зарегистрирован'], 201);
    }
}