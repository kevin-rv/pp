<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class EventFixtures extends Fixture implements DependentFixtureInterface
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
        for ($i = $j = $k = 0; $i < 1000; ++$i) {
            $events = new Event();
            $events->setShortDescription($this->faker->text(45));
            $events->setFullDescription($this->faker->realText());
            $events->setStartDatetime($this->faker->dateTime);
            $events->setEndDatetime($this->faker->dateTime);
            $events->setPlanning($this->getReference('planning_'.$j));
            $events->addContact($this->getReference('contact_'.$k));
            ++$j;
            if (100 === $j) {
                $j = 0;
            }
            ++$k;
            if (500 === $k) {
                $k = 0;
            }

            // table intermédiaire ?
            $manager->persist($events);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PlanningFixtures::class,
            ContactFixtures::class,
        ];
    }
}
