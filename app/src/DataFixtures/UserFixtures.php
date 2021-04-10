<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class UserFixtures extends Fixture
{
    public const DEFAULT_USER_LOGIN = 'luke';

    public const DEFAULT_USER_PASSWORD = 'useTheForce';

    public const USER_LOGIN_ROLE_ADMIN = 'ben';

    public const USER_PASSWORD_ROLE_ADMIN = 'jediMaster';

    public function load(ObjectManager $manager): void
    {
        $this->createUser($manager, self::DEFAULT_USER_LOGIN, self::DEFAULT_USER_PASSWORD, ['ROLE_USER']);
        $this->createUser($manager, self::USER_LOGIN_ROLE_ADMIN, self::USER_PASSWORD_ROLE_ADMIN, ['ROLE_ADMIN']);
    }

  /**
   * @param string[] $roles
   */
    private function createUser(ObjectManager $manager, string $login, string $password, array $roles): void
    {
        $userEntity = new User();
        $userEntity->setLogin($login);
        $userEntity->setPlainPassword($password);
        $userEntity->setRoles($roles);
        $manager->persist($userEntity);
        $manager->flush();
    }
}
