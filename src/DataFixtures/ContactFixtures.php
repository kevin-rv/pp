<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ContactFixtures extends Fixture
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
        $contact = new Contact();
        $contact->setName($this->faker->word);
        $contact->setPhone_Number($this->faker->phoneNumber);
        $contact->setHome($this->faker->address);
        $contact->setBirthday($this->faker->dateTime);
        $contact->setEmail($this->faker->email);
        $contact->setRelationsships($this->faker->word);
        $contact->setWork($this->faker->jobTitle);
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
