<?php


namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository              $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    public function checkUserExistsByLogin(string $login): bool
    {
        return $this->userRepository->existsByLogin($login);
    }

    public function createNewUser(string $login, string $password, string $firstName, string $lastName): User
    {
        $user = new User();
        $user->setLogin($login)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setRoles(['ROLE_USER']);

        $encodedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($encodedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAll();

        return array_values(array_filter(array_map(function ($user) {
            if (in_array('ROLE_USER', $user->getRoles(), true)) {
                return [
                    'id' => $user->getId(),
                    'login' => $user->getLogin(),
                    'roles' => $user->getRoles(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                ];
            }
            return null;
        }, $users)));
    }

    public function updateUser(int $id, array $data): array
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw new UserNotFoundException("Пользователь с ID $id не найден");
        }

        if (isset($data['login'])) {
            $user->setLogin(trim($data['login']));
        }

        if (isset($data['firstname'])) {
            $user->setFirstname(trim($data['firstname']));
        }

        if (isset($data['lastname'])) {
            $user->setLastname(trim($data['lastname']));
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'roles' => $user->getRoles(),
        ];
    }

}
