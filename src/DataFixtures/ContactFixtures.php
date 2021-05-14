<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ContactFixtures extends Fixture implements DependentFixtureInterface
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
    { //10 contact par utilisateur
        for ($i = $j = 0; $i < 500; ++$i) {
            $contact = new Contact();
            $contact->setName($this->faker->word);
            $contact->setPhoneNumber($this->faker->phoneNumber);
            $contact->setHome($this->faker->address);
            $contact->setBirthday($this->faker->dateTime);
            $contact->setEmail($this->faker->email);
            $contact->setRelationship($this->faker->word);
            $contact->setWork($this->faker->jobTitle);
            $contact->setUser($this->getReference('user_'.$j));
            ++$j;
            if (50 === $j) {
                $j = 0;
            }

            $this->setReference('contact_'.$i, $contact);

            $manager->persist($contact);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
} // TODO implémenter contact utilisateur et evenement
