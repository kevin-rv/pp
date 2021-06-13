<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    { // 10 tache par planning
        for ($i = $j = 0; $i < 1000; ++$i) {
            $task = new Task();
            $task->setShortDescription($this->faker->text(45));
            $task->setDone($this->faker->dateTime);
            $task->setDoneLimitDate($this->faker->dateTime);
            $task->setPlanning($this->getReference('planning_'.$j++));
            if (100 === $j) {
                $j = 0;
            }

            $manager->persist($task);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PlanningFixtures::class,
        ];
    }
} // TODO implémenter au planning
