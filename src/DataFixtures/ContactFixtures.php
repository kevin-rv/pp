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
        for ($i = 0; $i < 500; $i++) {
            $contact = new Contact();
            $contact->setName($this->faker->word);
            $contact->setPhoneNumber($this->faker->phoneNumber);
            $contact->setHome($this->faker->address);
            $contact->setBirthday($this->faker->dateTime);
            $contact->setEmail($this->faker->email);
            $contact->setRelationship($this->faker->word);
            $contact->setWork($this->faker->jobTitle);
            $contact->setUser($this->getReference('user_'.$i));
            $contact->setEvent($this->getReference('event_'.$i));
// Table intermédiaire ?
            $manager->persist($contact);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            EventFixtures::class,
        ];
    }
} // TODO implémenter contact utilisateur et evenement
