<?php

namespace App\Service;

use App\Entity\FavoriteTask;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\FavoriteTaskRepository;
use Doctrine\ORM\EntityManagerInterface;

class FavoriteTaskService
{
    private FavoriteTaskRepository $favoriteTaskRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(FavoriteTaskRepository $favoriteTaskRepository, EntityManagerInterface $entityManager)
    {
        $this->favoriteTaskRepository = $favoriteTaskRepository;
        $this->entityManager = $entityManager;
    }

    public function toggleFavoriteTask(User $user, Task $task): bool
    {
        if ($this->favoriteTaskRepository->isTaskFavorite($user, $task)) {
            $this->favoriteTaskRepository->removeFavoriteTask($user, $task);
            return false; // Удалено из избранного
        }

        $favoriteTask = new FavoriteTask($user, $task);
        $this->entityManager->persist($favoriteTask);
        $this->entityManager->flush();

        return true; // Добавлено в избранное
    }
}
