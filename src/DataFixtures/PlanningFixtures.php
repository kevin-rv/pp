<?php

namespace App\DataFixtures;

use App\Entity\Planning;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class PlanningFixtures extends AbstractSuperFixture implements DependentFixtureInterface
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
    {
        for ($i = $j = 0; $i < 100; ++$i) {
            $planning = new Planning();
            $planning->setName($this->faker->word);
            $planning->setUser($this->getReference('user_'.$j));
            ++$j;
            if (50 === $j) {
                $j = 0;
            }

            $this->setReference('planning_'.$i, $planning);

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
