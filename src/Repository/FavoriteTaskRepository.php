<?php

namespace App\Repository;

use App\Entity\FavoriteTask;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FavoriteTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FavoriteTask::class);
    }

    public function isTaskFavorite(User $user, Task $task): bool
    {
        return (bool) $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.task = :task')
            ->setParameter('user', $user)
            ->setParameter('task', $task)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeFavoriteTask(User $user, Task $task): void
    {
        $favoriteTask = $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.task = :task')
            ->setParameter('user', $user)
            ->setParameter('task', $task)
            ->getQuery()
            ->getOneOrNullResult();

        if ($favoriteTask) {
            $this->_em->remove($favoriteTask);
            $this->_em->flush();
        }
    }
}
