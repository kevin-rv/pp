<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class TaskFixtures extends Fixture
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
        $task = new Task();
        $task->setShortDescription($this->faker->text);
        $task->setDone($this->faker->date(20210803));
        $task->setDone_limite_date($this->faker->dateTime);
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
