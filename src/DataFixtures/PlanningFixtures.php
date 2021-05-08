<?php

namespace App\DataFixtures;

use App\Entity\Planning;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class PlanningFixtures extends Fixture implements DependentFixtureInterface
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
        for ($i = $j = 0; $i < 100; $i++) {
            $planning = new Planning();
            $planning->setName($this->faker->word);
            $planning->setUser($this->getReference('user_'.$j));
            $j++;
            if ($j === 50) {
                $j = 0;
            }

            $manager->persist($planning);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
