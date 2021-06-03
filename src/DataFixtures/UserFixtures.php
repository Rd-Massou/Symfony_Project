<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /* Cette classe nous permet de populer la base de données par des utilisateurs de teste juste pour voir le
    fonctionnement de l'application avec des données aritificiel avant de passer en prod
    */
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }
    
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername("Ahmed ZELLOU");
        $user->setEmail("ahmed.zellou@um5.ac.ma");
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, "BEM_BI_2022"));
        $user->setRoles(["ROLE_USER"]);
        $manager->persist($user);

        $user = new User();
        $user->setUsername("Kaoutar BOUCHDAK");
        $user->setEmail("kaoutar.bouchdak@um5.ac.ma");
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, "BEM_BI_2022"));
        $user->setRoles(["ROLE_ADMIN"]);
        $manager->persist($user);

        $user = new User();
        $user->setUsername("Majda EL AM");
        $user->setEmail("majda.elam@um5.ac.ma");
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, "BEM_BI_2022"));
        $user->setRoles(["ROLE_ADMIN"]);
        $manager->persist($user);

        $user = new User();
        $user->setUsername("Rida MASSOU");
        $user->setEmail("rida.massou@um5.ac.ma");
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, "BEM_BI_2022"));
        $user->setRoles(["ROLE_ADMIN"]);
        $manager->persist($user);

        $manager->flush();
    }
}
