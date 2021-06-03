<?php

namespace App\Controller;

use App\Entity\Storage;
use App\Form\StorageType;
use App\Repository\StorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/* Contrairement à HomeController, ici on définit la route du controlleur entièrement. Par la suite,
nous seront en mesure de prendre la meme route et composer la route de chacune des méthodes. On peut 
voir cette route comme un radical 
*/

/**
 * @Route("/storage")
 */
class StorageController extends AbstractController
{
    /**
     * @Route("/", name="app_storage", methods={"GET"})
     */
    public function index(StorageRepository $storageRepository): Response
    {
        /* On retourn la page index des stockage ou nous listant tout les stockage qu'on a au dépot du magisin,
        ceux-ci sont récupéré de la base données en utilisant le repository de l'entité Storage et sa méthode
        findAll.
        */
        return $this->render('storage/index.html.twig', [
            'storages' => $storageRepository->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    /* Lors de la création d'un nouveau stockage au dépot du magasin, cette route est demandé. Seul l'admin peut
    faire cette action.
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/new", name="app_storage_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        /* On génère une instance de l'entité Storage et on appelle le formulaire relatif à celui-ci */
        $storage = new Storage();
        $form = $this->createForm(StorageType::class, $storage);

        /* Puisque le formulaire envoi une requête POST pour pouvoir envoyer les données saisies par l'admin,
        on les traite par cette ligne
        */
        $form->handleRequest($request);

        /* On ne fait le traitement des information qu'après l'envoi et la validation du formulaire. Ce
        block sert à inserer le nouveau stockage et rediriger l'admin vers l'index des stockages.
        */
        if ($form->isSubmitted() && $form->isValid()) {
            // On initialise la quantité stockée par 0, le status est rempli automatiquement
            $quantity = 0;
            $storage->setQuantity($quantity);

            // On insère le nouveau stockage dans la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($storage);
            $entityManager->flush();

            // Redirection vers l'index de stockage
            return $this->redirectToRoute('app_storage');
        }

        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'admin
        return $this->render('storage/new.html.twig', [
            'storage' => $storage,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    // Cette méthode sert à visualiser les details d'un stockage séléctionné.

    /**
     * @Route("/{id}", name="app_storage_show", methods={"GET"})
     */
    public function show(Storage $storage): Response
    {
        /* On retourn la page de details du stockage séléctionné. Celui-ci est récupéré de la base données en
        utilisant l'id spécifié dans la route sans appeler le repository explicitement. Et on le passe comme
        parametre à twig
        */
        return $this->render('storage/show.html.twig', [
            'storage' => $storage,
            'user' => $this->getUser(),
        ]);
    }

    /* Cette méthode sert à modifier un stockage séléctionné. Elle renvoi le formulaire relatif au stockage
    pré-rempli par les données deja existant. La modification du stockage est résérvée seulement pour l'admin!
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}/edit", name="app_storage_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Storage $storage): Response
    {
        /* On génère le formulaire relatif au stockage en le populant de donnée à partir de l'instance de Storage
        récupéré de la base de données à partir de l'id spécifié dans la route
        */
        $form = $this->createForm(StorageType::class, $storage);

        /* Puisque le formulaire envoi une requête POST pour pouvoir envoyer les données saisies par l'admin,
        on les traite par cette ligne
        */
        $form->handleRequest($request);

        /* On ne fait le traitement des information qu'après l'envoi et la validation du formulaire. Ce
        block sert à modifier la vente et rediriger l'admin vers l'index de stockage.
        */
        if ($form->isSubmitted() && $form->isValid()) {
            // On fait les modification nécéssaires dans la base de donnée
            $this->getDoctrine()->getManager()->flush();

            // Redirection vers l'index de stockage
            return $this->redirectToRoute('app_storage');
        }

        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'admin
        return $this->render('storage/edit.html.twig', [
            'storage' => $storage,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    /* Cette méthode est appelé lors d'un appel à la suppression d'un stockage séléctionné. La supression est
    réservée pour l'admin seulement afin de proteger les informations du magasin.
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}", name="app_storage_delete", methods={"POST"})
     */
    public function delete(Request $request, Storage $storage): Response
    {
        /* Pour des raison de sécurité, nous vérifions le jeton csrf si il est bien conforme au jeton de 
        notre formulaire ou il a été falsifié avant d'éffectuer la suppréssion du stockage.
        */
        if ($this->isCsrfTokenValid('delete'.$storage->getId(), $request->request->get('_token'))) {
            // On supprime le stockage de la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($storage);
            $entityManager->flush();
        }

        // Redirection vers l'index de stockage
        return $this->redirectToRoute('app_storage');
    }
}
