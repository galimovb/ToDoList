<?php

namespace App\Controller;

use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard', methods: 'GET')]
    public function index(LoggerInterface $logger): Response
    {
        $logger->info('Админ страница загружена', [
            'user' => $this->getUser(),
        ]);

        return $this->render('admin/index.html.twig', [
            'vue_component' => 'admin-page',
        ]);
    }

    #[Route('/admin/users', name: 'admin_users', methods: 'GET')]
    public function getUsers(LoggerInterface $logger, UserService $userService): JsonResponse
    {
        $logger->info('Админ запросил список пользователей ', [
            'users' => $userService->getAllUsers()
        ]);
        return new JsonResponse($userService->getAllUsers(), 200);
    }

    #[Route('/admin/users/{id}', name: 'edit_user', methods: ['PUT'])]
    public function editUser(Request $request, int $id, UserService $userService, LoggerInterface $logger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            $logger->error("Ошибка обновления пользователя: {$id}", ['error' => 'Некорректные данные']);
            return new JsonResponse(['error' => 'Некорректные данные'], 400);
        }

        try {
            $updatedUser = $userService->updateUser($id, $data);
            $logger->info("Пользователь успешно обновлен", ['user_id' => $id]);

            return new JsonResponse($updatedUser, 200);
        } catch (UserNotFoundException $e) {
            $logger->warning("Попытка редактировать несуществующего пользователя: {$id}");
            return new JsonResponse(['error' => 'Пользователь не найден'], 404);
        } catch (\Exception $e) {
            $logger->error("Ошибка при редактировании пользователя: {$id}", ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Ошибка сервера'], 500);
        }
    }

}
