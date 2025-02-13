<?php

namespace App\Controller;

use App\Entity\FavoriteTask;
use App\Repository\FavoriteTaskRepository;
use App\Repository\TaskRepository;
use App\Service\FavoriteTaskService;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    private TaskService $taskService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(TaskService $taskService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->taskService = $taskService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/tasks/{type}', name: 'get_tasks', methods: ['GET'])]
    public function getTasks(Request $request, string $type, TaskRepository $taskRepository): JsonResponse
    {
        $user = $this->getUser();
        $this->logger->info("Пользователь запросил  задач типа {$type}", ['user' => $user]);

        if ($type == 'today') {
            $responseData = $this->taskService->getTodayTasks($user);
            return new JsonResponse($responseData, 200);
        }
        if ($type == 'planned') {
            $responseData = $this->taskService->getPlannedTasks($user);
            return new JsonResponse($responseData, 200);
        }

        $this->logger->warning("Пользователь запросил задачи несуществующего типа: {$type}");
        return new JsonResponse(['message' => 'Таких задач нет'], 404);
    }

    #[Route('/tasks/new', name: 'new_task', methods: ['POST'])]
    public function saveTask(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $this->logger->info('Создание новой задачи', ['user' => $user]);

        $task = $this->taskService->createTask($data, $user);

        $dueDate = $task->getDueDate();
        if ($dueDate) {
            $dueDate->setTimezone(new \DateTimeZone('Europe/Moscow'));
        }

        $this->logger->info('Задача успешно создана', ['task' => $task]);

        return new JsonResponse([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'dueDate' => $dueDate?->format('Y-m-d H:i:s'),
            'priority' => $task->getPriority(),
            'isCompleted' => $task->getIsCompleted(),
        ], 201);
    }

    #[Route('/tasks/complete/{id}', name: 'complete_task', methods: ['PATCH'])]
    public function completeTask(Request $request, int $id, TaskRepository $taskRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $task = $taskRepository->find($id);

        if (!$task) {
            $this->logger->warning("Попытка завершить несуществующую задачу: {$id}");
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $task->setIsCompleted($data['isCompleted'] ?? true);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->logger->info("Задача успешно завершена", ['task_id' => $id]);
        return new JsonResponse(['message' => 'Task updated successfully'], 200);
    }

    #[Route('/tasks/edit/{id}', name: 'edit_task', methods: ['PUT'])]
    public function editTask(Request $request, int $id, TaskService $taskService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $task = $taskService->updateTask($id, $data);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Ошибка при редактировании задачи: {$id}", ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        if (!$task) {
            $this->logger->warning("Попытка редактировать несуществующую задачу: {$id}");
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $this->logger->info("Задача успешно изменена", ['task_id' => $id]);

        return new JsonResponse([
            'task' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'dueDate' => $task->getDueDate()?->format('Y-m-d H:i:s'),
                'priority' => $task->getPriority(),
                'isCompleted' => $task->getIsCompleted(),
            ]
        ], 200);
    }

    #[Route('/tasks/delete/{id}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(Request $request, int $id, TaskRepository $taskRepository): JsonResponse
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            $this->logger->warning("Попытка удалить несуществующую задачу: {$id}");
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->logger->info("Задача удалена", ['task_id' => $id]);
        return new JsonResponse(['message' => 'Task deleted successfully'], 200);
    }

    #[Route('/tasks/favorite/{id}', name: 'toggle_favorite_task', methods: ['POST'])]
    public function toggleFavoriteTask(int $id, TaskRepository $taskRepository, FavoriteTaskRepository $favoriteTaskRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $task = $taskRepository->find($id);
        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $favoriteTask = $favoriteTaskRepository->findOneBy(['user' => $user, 'task' => $task]);

        if ($favoriteTask) {
            // Если задача уже в избранном, удаляем её
            $entityManager->remove($favoriteTask);
            $message = 'Задача удалена из избранного';
        } else {
            // Добавляем в избранное
            $favoriteTask = new FavoriteTask();
            $favoriteTask->setUser($user);
            $favoriteTask->setTask($task);
            $entityManager->persist($favoriteTask);
            $message = 'Задача добавлена в избранное';
        }

        $entityManager->flush();
        return new JsonResponse(['message' => $message], 200);
    }

}
