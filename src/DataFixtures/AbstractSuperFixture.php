<?php


namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;

abstract class AbstractSuperFixture extends Fixture
{
    public static $references = [];

    public function setReference($name, $object)
    {
        if (!isset(self::$references[get_class($object)])) {
            self::$references[get_class($object)] = [];
        }

        self::$references[get_class($object)][] = $object;

        parent::setReference($name, $object);
    }

    public function getRandomReference(string $class)
    {
        return self::$references[$class][array_rand(self::$references[$class])];
    }
}
