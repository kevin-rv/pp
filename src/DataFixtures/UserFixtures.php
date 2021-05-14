<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class UserFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 50; ++$i) {
            $user = new User();
            $user->setName($this->faker->word);
            $user->setBirthday($this->faker->dateTime('-18 years'));
            $user->setEmail('email_'.$i.'@email.com');
            $user->setHome($this->faker->address);
            $user->setPhoneNumber($this->faker->phoneNumber);
            $user->setPassword('password');
            $user->setWork($this->faker->jobTitle);

            $this->setReference('user_'.$i, $user);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
