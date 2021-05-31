<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function index(): Response
    {
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
        if ($this->getUser()->hasRole('ROLE_ADMIN')){
            return $this->redirect($this->generateUrl('app_dashboard'));
        } else {
            return $this->redirect($this->generateUrl('app_homepage'));
        }
    }

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
