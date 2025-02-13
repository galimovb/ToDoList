<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Task::class);
        $this->logger = $logger;
    }
    /**
     * @throws \Exception
     */
    public function findTasksDueToday(User $user): array
    {
        $localTimezone = new \DateTimeZone('+03:00');
        $todayStart = new \DateTime('today', $localTimezone);
        $todayEnd = new \DateTime('tomorrow', $localTimezone);

        $todayStart->setTimezone(new \DateTimeZone('UTC'));
        $todayEnd->setTimezone(new \DateTimeZone('UTC'));

        $query = $this->createQueryBuilder('t')
            ->select('t, 
                  CASE WHEN ft.id IS NOT NULL THEN true ELSE false END AS is_favorite')
            ->leftJoin('App\Entity\FavoriteTask', 'ft', 'WITH', 'ft.task = t AND ft.user = :user')
            ->andWhere('t.dueDate >= :todayStart')
            ->andWhere('t.dueDate < :todayEnd')
            ->andWhere('t.isCompleted = :isCompleted')
            ->andWhere('t.user = :user')
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd)
            ->setParameter('isCompleted', false)
            ->setParameter('user', $user)
            ->getQuery();

        $this->logger->info('SQL запрос для поиска задач на сегодня', ['sql' => $query->getSQL()]);
        $result = $query->getResult();
        $this->logger->info('Найденные задачи на сегодня', ['count' => count($result)]);

        return array_map(function ($row) {
            return [
                'task' => $row[0], // сама задача
                'is_favorite' => (bool) $row['is_favorite'] // true или false
            ];
        }, $result);
    }


    public function findPlannedTasks(User $user): array
    {
        $moscowTimezone = new \DateTimeZone('Europe/Moscow');

        // Завтра и 2 недели в будущем (в московском времени)
        $startDate = new \DateTime('tomorrow', $moscowTimezone);
        $startDate->setTime(0, 0);

        $endDate = (clone $startDate)->modify('+2 weeks')->setTime(23, 59, 59);

        // Переводим в UTC перед запросом (если БД хранит dueDate в UTC)
        $startDateUtc = (clone $startDate)->setTimezone(new \DateTimeZone('UTC'));
        $endDateUtc = (clone $endDate)->setTimezone(new \DateTimeZone('UTC'));

        $query = $this->createQueryBuilder('t')
            ->andWhere('t.dueDate >= :startDate')
            ->andWhere('t.dueDate <= :endDate')
            ->andWhere('t.isCompleted = :isCompleted')
            ->andWhere('t.user = :user')
            ->setParameter('startDate', $startDateUtc)
            ->setParameter('endDate', $endDateUtc)
            ->setParameter('isCompleted', false)
            ->setParameter('user', $user)
            ->getQuery();

        $this->logger->info('SQL запрос для запланированных задач', [
            'startDate' => $startDateUtc->format('Y-m-d H:i:s'),
            'endDate' => $endDateUtc->format('Y-m-d H:i:s'),
            'sql' => $query->getSQL()
        ]);

        return $query->getResult();
    }



}
