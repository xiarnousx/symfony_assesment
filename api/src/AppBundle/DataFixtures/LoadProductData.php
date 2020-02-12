<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadProductData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $product = new Product();
        $product->setName('iPhone X');
        $product->setPrice(999.99);
        $product->setImage('https://dummyimage.com/200x200/000/fff');

        $manager->persist($product);
        $manager->flush();
    }
}
