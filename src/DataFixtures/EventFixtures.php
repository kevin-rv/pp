<?php

namespace App\DataFixtures;
use App\Entity\Contact;
use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class EventFixtures extends Fixture implements  DependentFixtureInterface
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
        for ($i = 0; $i < 1000; $i++) {
            $event = new Event();
            $event->setShortDescription($this->faker->text);
            $event->setFullDescription($this->faker->realText());
            $event->setStartDatetime($this->faker->dateTime);
            $event->setEndDatetime($this->faker->dateTime);
            $event->setPlanning($this->getReference('planning_'.$i));
            $event->setContact($this->getReference('contact_'.$i));

 // table intermédiaire ?
            $manager->persist($event);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PlanningFixtures::class,
            Contact::class,
        ];
    }
} // TODO implémenter planning et contact
