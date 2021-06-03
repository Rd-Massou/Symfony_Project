<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    /* Cette classe nous permet de populer la base de données par des catégories de teste juste pour voir le
    fonctionnement de l'application avec des données aritificiel avant de passer en prod
    */
    public function load(ObjectManager $manager)
    {
        for($i=0; $i<10; $i++){
            $category = new Category();
            $category->setTitle("Category ".$i);
            $this->addReference('categ'.$i, $category);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
