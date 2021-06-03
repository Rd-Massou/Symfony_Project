<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class HomeController extends AbstractController
{
    /* À l'aide de l'annotation @Route, nous définissons la route et le nom de route permetant d'acceder à 
    cette action index du controlleur. Elle permet de rendre la page home à l'utilsateur si le path dans 
    notre site est conforme à la route qu'on a défini.
    */

    /**
     * @Route("/", name="app_homepage")
     */
    public function index(): Response
    {
        /* On rend le page home.html.twig en lui envoyant aussi comme paramètres le nom du controlleur
        ainsi que l'utilisateur actuel connecté.
        */
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/role_based_redirect", name="app_redirect")
     */
    public function redirecting(): Response
    {
        /* Ce block nous permet de rediriger l'utilisateur actuel connecté selon son role vers une page
        bien précise. Dans ce cas c'est la page home pour l'utilisateur nomal, mais pour l'admin c'est
        la page du dashboard contenant des infomations sur l'ensemble d'activités effectué dans notre
        magasin.
        */
        if ($this->getUser()->hasRole('ROLE_ADMIN')){
            return $this->redirect($this->generateUrl('app_dashboard'));
        } else {
            return $this->redirect($this->generateUrl('app_homepage'));
        }
    }

    /* L'annotation @isGranted nous permet de définir une autorisation d'acces à la méthode dashboard. 
    C'est à dire que si notre utilisateur n'a pas le role d'admin, il n'est pas autorisé d'accéder à cette
    page
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/dashboard", name="app_dashboard")
     */
    public function dashboard(): Response
    {
        return $this->render('home/dashboard.html.twig', [
            'controller_name' => 'HomeController',
            'user' => $this->getUser()
        ]);
    }
}
