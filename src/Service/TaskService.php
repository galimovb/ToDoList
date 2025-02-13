<?php


namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    private $entityManager;
    private TaskRepository $taskRepository;

    public function __construct(EntityManagerInterface $entityManager,TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
    }

    public function createTask(array $taskData, User $user): Task
    {
        $task = new Task();

        $task->setTitle($taskData['title']);
        $task->setDescription($taskData['description'] ?? null);

        $taskDataString = $taskData['dueDate'];

        if ($taskDataString) {
            $dueDate = new \DateTime($taskDataString);
        } else {
            $dueDate = null;
        }

        $task->setDueDate($dueDate);
        $task->setPriority($taskData['priority'] ?? null);
        $task->setIsCompleted($taskData['isCompleted'] ?? false);


        $task->setUser($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }


    public function getTodayTasks(User $user): array
    {
        $tasksWithFavorites = $this->taskRepository->findTasksDueToday($user);

        return array_map(function (array $taskData) {
            $task = $taskData['task'];
            $isFavorite = $taskData['is_favorite'];

            $dueDate = $task->getDueDate();
            if ($dueDate) {
                $dueDate->setTimezone(new \DateTimeZone('Europe/Moscow'));
                $formattedDueDate = $dueDate->format('Y-m-d H:i:s');
            } else {
                $formattedDueDate = null;
            }

            return [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'dueDate' => $formattedDueDate,
                'isCompleted' => $task->getIsCompleted(),
                'priority' => $task->getPriority(),
                'isFavorite' => $isFavorite, // Добавляем статус избранного
            ];
        }, $tasksWithFavorites);
    }


    public function getPlannedTasks(User $user): array
    {
        $tasks = $this->taskRepository->findPlannedTasks($user);

        // Группируем задачи по датам
        $groupedTasks = [];
        $timezone = new \DateTimeZone('Europe/Moscow');

        foreach ($tasks as $task) {
            $dueDate = (clone $task->getDueDate())->setTimezone($timezone)->format('Y-m-d');

            if (!isset($groupedTasks[$dueDate])) {
                $groupedTasks[$dueDate] = [];
            }

            $groupedTasks[$dueDate][] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'dueDate' => $task->getDueDate()->setTimezone($timezone)->format('Y-m-d H:i:s'),
                'isCompleted' => $task->getIsCompleted(),
                'priority' => $task->getPriority(),
            ];
        }

        $startDate = new \DateTime('tomorrow', $timezone);
        $startDate->setTime(0, 0);
        $endDate = (clone $startDate)->modify('+13 days');

        // Генерируем даты и задачи
        $responseData = [];
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $responseData[] = [
                'date' => $formattedDate,
                'tasks' => $groupedTasks[$formattedDate] ?? [],
            ];
        }

        return $responseData;
    }



    public function updateTask(int $id, array $data): ?Task
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return null;
        }

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['dueDate'])) {
            try {
                $task->setDueDate(new \DateTime($data['dueDate']));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format');
            }
        }
        if (isset($data['priority'])) {
            $task->setPriority((int) $data['priority']);
        }
        if (isset($data['isCompleted'])) {
            $task->setIsCompleted((bool) $data['isCompleted']);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}
