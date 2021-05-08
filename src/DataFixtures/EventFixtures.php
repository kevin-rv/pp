<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class EventFixtures extends Fixture
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
        $event = new Event();
        $event->setShort_Description($this->faker->text);
        $event->setFull_Description($this->faker->realText());
        $event->setStart_Datetime($this->faker->dateTime);
        $event->setEnd_Datetime($this->faker->dateTime);
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
