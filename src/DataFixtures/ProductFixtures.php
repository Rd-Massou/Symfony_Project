<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    /* Cette classe nous permet de populer la base de données par des Produits de teste juste pour voir le
    fonctionnement de l'application avec des données aritificiel avant de passer en prod
    */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function load(ObjectManager $manager)
    {
        for($i=0; $i<10; $i++){
            $product = new Product();
            $product->setTitle("Product ".$i);
            $product->setPrice($i * 313.65);
            $product->setCategory($this->getReference('categ'.$i));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
