<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;


// https://github.com/fzaninotto/Faker?tab=readme-ov-file#fakerproviderbase

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $user = new User();
        $user->setEmail('hector.bidan@gmail.com');
        $user->setFirstname('Hector');
        $user->setLastname('Bidan');
        $user->setAddress('1 rue de la Paix');
        $user->setCity('Paris');
        $user->setZip('75000');
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'aze'));

        $manager->persist($user);

        $nb_users = 105;
        $users = [];
        for ($i = 0; $i < $nb_users; $i++) {
            $users[$i] = new User();

            $users[$i]->setEmail($faker->email);
            $users[$i]->setFirstname($faker->firstName);
            $users[$i]->setLastname($faker->lastName);
            $users[$i]->setAddress($faker->address);
            $users[$i]->setCity($faker->city);
            $users[$i]->setZip($faker->postcode);
            $users[$i]->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s')));
            $users[$i]->setRoles(['ROLE_USER']);
            $users[$i]->setPassword($this->passwordHasher->hashPassword($users[$i], $faker->password));

            $manager->persist($users[$i]);
        }

        $manager->flush();
    }
}
