<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Form\PurchaseType;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/* Contrairement à HomeController, ici on définit la route du controlleur entièrement. Par la suite,
nous seront en mesure de prendre la meme route et composer la route de chacune des méthodes. On peut 
voir cette route comme un radical 
*/

/**
 * @Route("transactions/purchases")
 */
class PurchaseController extends AbstractController
{
    /**
     * @Route("/", name="app_purchases", methods={"GET"})
     */
    public function index(PurchaseRepository $purchaseRepository): Response
    {
        /* On retourn la page index des achats ou nous listant tout les achats qu'on a effectué pour le magisin
        (approvisionnement), ceux-ci sont récupéré de la base données en utilisant le repository de l'entité
        Purchase et sa méthode findAll.
        */
        return $this->render('transactions/purchase/index.html.twig', [
            'purchases' => $purchaseRepository->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    /* Lors de la création d'un nouveau achat/approvisionnement pour le magasin, cette route est demandé.
    */

    /**
     * @Route("/new", name="app_purchases_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        /* On génère une instance de l'entité Purchase et on appelle le formulaire relatif à celui-ci */
        $purchase = new Purchase();
        $form = $this->createForm(PurchaseType::class, $purchase);

        /* Puisque le formulaire envoi une requête POST pour pouvoir envoyer les données saisies par 
        l'utilisateur, on les traite par cette ligne
        */
        $form->handleRequest($request);

        /* On ne fait le traitement des information qu'après l'envoi et la validation du formulaire. Ce
        block sert à inserer le nouveau approvisionement et rediriger l'utilisateur à l'index des achats.
        */
        if ($form->isSubmitted() && $form->isValid()) {
            /* Lors de l'approvisionement, on doit faire la mise à jour du stock pour eviter le mal
            fonctionnement du magasin du à la non fiabilité des données. 
            */
            $storage = $purchase->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() + $purchase->getQuantity());

            // On enregistre la valeur du la nouvelle quantité acheté pour garder la trace
            $purchase->setOldQuantity($purchase->getQuantity());

            // On calcule le total de l'achat effectué
            $purchase->setTotal($form->get('supplyPrice')->getData() * $purchase->getQuantity());

            // Inserer l'achat dans la base donnée 
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($purchase);
            $entityManager->flush();

            // Redirection vers l'index des achats
            return $this->redirectToRoute('app_purchases');
        }

        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'utilisateur
        return $this->render('transactions/purchase/new.html.twig', [
            'purchase' => $purchase,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    // Cette méthode sert à visualiser les détails d'un achat séléctionné.

    /**
     * @Route("/{id}", name="app_purchases_show", methods={"GET"})
     */
    public function show(Purchase $purchase): Response
    {
        /* On retourn la page de details de l'achat séléctionné. Celui-ci est récupéré de la base données en
        utilisant l'id spécifié dans la route sans appeler le repository explicitement. Et on le passe 
        comme parametre à twig
        */
        return $this->render('transactions/purchase/show.html.twig', [
            'purchase' => $purchase,
            'user' => $this->getUser(),
        ]);
    }

    /* Cette méthode sert à modifier un achat séléctionné. Elle renvoi le formulaire relatif à l'achat
    pré-rempli par les données deja existant. La modification de l'achat est résérvée seulement pour l'admin!
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}/edit", name="app_purchases_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Purchase $purchase, ValidatorInterface $validator): Response
    {
        /* On génère le formulaire relatif au achats en le populant de donnée à partir de l'instance de Purchase
        récupéré de la base de données à partir de l'id spécifié dans la route
        */
        $form = $this->createForm(PurchaseType::class, $purchase);

        /* Puisque le formulaire envoi une requête POST pour pouvoir envoyer les données saisies par l'admin,
        on les traite par cette ligne
        */
        $form->handleRequest($request);

        /* On ne fait le traitement des information qu'après l'envoi et la validation du formulaire. Ce
        block sert à modifier l'achat et rediriger l'admin vers l'index des achats.
        */
        if ($form->isSubmitted() && $form->isValid()) {
            /* Lors de l'approvisionement, on doit faire la mise à jour du stock pour eviter le mal
            fonctionnement du magasin du à la non fiabilité des données. 
            */
            $storage = $purchase->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() - $purchase->getQuantity());

            /* On valide les contraintes posé dans l'entité Stockage afin d'assurer l'intégrité de données avant
            de proceder aux modifications au niveau de la base de données.
            */
            $errors = $validator->validate($storage);
            if (count($errors) === 0) {
                /* Si tout se passe bien et les données saisies ne pose pas de problème, on modifie le 
                stockage du produit converné */
                $purchase->setOldQuantity($purchase->getQuantity());

                // On calcule le nouveau total de l'achat fait
                $purchase->setTotal($purchase->getProduct()->getPrice() * $purchase->getQuantity());

                // Puis fait les modification nécéssaires dans la base de donnée
                $this->getDoctrine()->getManager()->flush();
    
                // Redirection vers l'index des ventes
                return $this->redirectToRoute('app_purchases');
            } else {
                /* Si on rencontre une erreur lors de la validation des contraintes, c'est que la modification
                entrainera un stockage négatif ce qui est impossible.
                */
                $storage->setQuantity($storage->getQuantity() + $purchase->getQuantity());
                $form->get('quantity')->addError(new FormError('You will cause a deficiency in storage with this quantity, Please reconsider!'));
            }
        }

        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'admin
        return $this->render('transactions/purchase/edit.html.twig', [
            'purchase' => $purchase,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    /* Cette méthode est appelé lors d'un appel à la suppression d'un achat séléctionné. La supression est
    réservée pour l'admin seulement afin de proteger les informations du magasin.
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}", name="app_purchases_delete", methods={"POST"})
     */
    public function delete(Request $request, Purchase $purchase): Response
    {
        /* Pour des raison de sécurité, nous vérifions le jeton csrf si il est bien conforme au jeton de 
        notre formulaire ou il a été falsifié avant d'éffectuer la suppréssion de l'achat.
        */
        if ($this->isCsrfTokenValid('delete'.$purchase->getId(), $request->request->get('_token'))) {
            // On fait la mise à jour du stockage du produit de l'achat converné
            $storage = $purchase->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() - $purchase->getQuantity());

            // On supprime l'achat et on fait les modifications nécessaires dans la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($purchase);
            $entityManager->flush();
        }

        // Redirection vers l'index des achats
        return $this->redirectToRoute('app_purchases');
    }
}
