<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/* Ce controlleur nous permet de gérer l'anthentification (connexion et déconnexion). Il assure la sécurité de
notre application puisque pour accéder aux différents pages nécéssite de s'authentifier et avoir quelques 
roles. Les roles et la sécurité est défini dans le fichier config/packages/sécurité.yaml
*/

class SecurityController extends AbstractController
{
    /* Cette méthode gère le login. Elle permet à l'utilisateur de s'authentifier en utilisant ses identifiants */
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // On vérifie si l'utilisateur est connecté pour lui donner l'accès à l'application selon son role
        if ($this->getUser()) {
            return $this->redirectToRoute('app_redirect');
        }

        // Récupéré l'erreur produit dans le login si jamais il en existe une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Le dernier identifiant entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        /* Si aucun utilisateur n'est connécté ou il essay d'acceder à une page par url sans être connécté,
        on le renvoi imperativement vers la page d'authentificaiton
        */
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    // Cette méthode même si elle est vide elle permet juste de gérer la déconnexion de l'utilisateur.

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {

    }
}
