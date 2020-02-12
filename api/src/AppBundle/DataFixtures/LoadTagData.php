<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTagData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tag = new Tag();
        $tag->setName('electronics');

        $manager->persist($tag);
        $manager->flush();
    }
}
