<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    
    public function load(ObjectManager $manager)
    {
        for($i=0; $i<10; $i++){
            $product = new Product();
            $product->setTitle("Product ".$i);
            $product->setPrice($i * 313.65);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
