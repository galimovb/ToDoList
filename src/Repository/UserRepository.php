<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, User::class);
        $this->logger = $logger;
    }

    public function existsByLogin(string $login): bool
    {
        $this->logger->info('Проверка существования пользователя', ['login' => $login]);

        $query = $this->createQueryBuilder('u')
            ->andWhere('u.login = :login')
            ->setParameter('login', $login)
            ->setMaxResults(1)
            ->getQuery();

        $this->logger->info('SQL запрос для проверки существования пользователя', ['sql' => $query->getSQL()]);

        $user = $query->getOneOrNullResult();
        $exists = $user !== null;

        $this->logger->info('Результат проверки существования пользователя', ['login' => $login, 'exists' => $exists]);

        return $exists;
    }


}
