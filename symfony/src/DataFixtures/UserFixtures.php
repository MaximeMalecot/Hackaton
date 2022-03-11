<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    const USER_ADMIN = 'admin';
    const USER_USER = 'user';

    /** @var UserPasswordHasherInterface $userPasswordHasher */
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $brand = (new Brand())
            ->setLabel('WiredBeauty')
            ->setIcon('wired_beauty_logo.png')
            ->setUpdatedAt(new \DateTime());
        $manager->persist($brand);
        $admin = (new User())
            ->setEmail('admin@admin.fr')
            ->setIsVerified(true)
            ->setRoles(['ROLE_ADMIN'])
            ->setBrand($brand)
        ;
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'test'));
        $manager->persist($admin);
        $this->setReference(self::USER_ADMIN, $admin);

        $user = (new User())
            ->setEmail('user@user.fr')
            ->setIsVerified(true)
            ->setRoles(['ROLE_USER'])
        ;
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'test'));
        $manager->persist($user);
        $this->setReference(self::USER_USER, $user);

        $manager->flush();
    }
}
