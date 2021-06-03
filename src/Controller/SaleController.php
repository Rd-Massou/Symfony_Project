<?php

namespace App\Controller;

use App\Entity\Sale;
use App\Form\SaleType;
use App\Repository\SaleRepository;
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
 * @Route("/transactions/sales")
 */
class SaleController extends AbstractController
{
    /**
     * @Route("/", name="app_sales", methods={"GET"})
     */
    public function index(SaleRepository $saleRepository): Response
    {
        /* On retourn la page index des ventes ou nous listant tout les ventes que fait le magisin,
        ceux-ci sont récupéré de la base données en utilisant le repository de l'entité Sale et sa méthode
        findAll.
        */
        return $this->render('transactions/sale/index.html.twig', [
            'sales' => $saleRepository->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/new", name="app_sales_new", methods={"GET","POST"})
     */
    public function new(Request $request, ValidatorInterface $validator): Response
    {
        /* On génère une instance de l'entité Sale et on appelle le formulaire relatif à celui-ci */
        $sale = new Sale();
        $form = $this->createForm(SaleType::class, $sale);

        /* Puisque le formulaire envoi une requête POST pour pouvoir envoyer les données saisies par
        l'utilisateur, on les traite par cette ligne
        */
        $form->handleRequest($request);
        
        /* On ne fait le traitement des information qu'après l'envoi et la validation du formulaire. Ce
        block sert à inserer la nouvelle vente et rediriger l'utilisateur vers l'index des ventes.
        */
        if ($form->isSubmitted() && $form->isValid()) {
            /* Lors de la vente, on doit faire la mise à jour du stock pour eviter le mal
            fonctionnement du magasin du à la non fiabilité des données. 
            */
            $storage = $sale->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() - $sale->getQuantity());

            /* On valide les contraintes posé dans l'entité Stockage afin d'assurer l'intégrité de données avant
            de proceder aux modifications au niveau de la base de données.
            */
            $errors = $validator->validate($storage);
            if (count($errors) === 0) {
                /* Si tout se passe bien et les données saisies ne pose pas de problème, on modifie le 
                stockage du produit converné */
                $sale->setOldQuantity($sale->getQuantity());

                // On calcule le total de la vente faite
                $sale->setTotal($sale->getProduct()->getPrice() * $sale->getQuantity());

                // Puis on insère la vente et la modification du stockage dans la base de donnée 
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($sale);
                $entityManager->flush();
    
                // Redirection vers l'index des ventes
                return $this->redirectToRoute('app_sales');
            } else {
                /* Si on rencontre une erreur lors de la validation des contraintes, on fait une sorte de 
                rollback et puis on affiche l'erreur adéquate.
                */
                $storage->setQuantity($storage->getQuantity() + $sale->getQuantity());
                if ($storage->getQuantity() === 0) {
                    $form->get('quantity')->addError(new FormError('Product not available!'));
                } else {
                    $form->get('quantity')->addError(new FormError('Quantity not available. Storage only contains '.$storage->getQuantity().' units!'));
                }
            }
        }

        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'utilisateur
        return $this->render('transactions/sale/new.html.twig', [
            'sale' => $sale,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    // Cette méthode sert à visualiser les details d'une vente séléctionnée.

    /**
     * @Route("/{id}", name="app_sales_show", methods={"GET"})
     */
    public function show(Sale $sale): Response
    {
        /* On retourn la page de details de la vente séléctionnée. Celui-ci est récupéré de la base données en
        utilisant l'id spécifié dans la route sans appeler le repository explicitement. Et on le passe 
        comme parametre à twig
        */
        return $this->render('transactions/sale/show.html.twig', [
            'sale' => $sale,
            'user' => $this->getUser(),
        ]);
    }

    /* Cette méthode sert à modifier une vente séléctionnée. Elle renvoi le formulaire relatif à la vente
    pré-rempli par les données deja existant. La modification de la vente est résérvée seulement pour l'admin!
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}/edit", name="app_sales_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Sale $sale, ValidatorInterface $validator): Response
    {
        /* On génère le formulaire relatif au ventes en le populant de donnée à partir de l'instance de Sale
        récupéré de la base de données à partir de l'id spécifié dans la route
        */
        $form = $this->createForm(SaleType::class, $sale);

        /* Puisque le formulaire envoi une requête POST pour pouvoir envoyer les données saisies par l'admin,
        on les traite par cette ligne
        */
        $form->handleRequest($request);

        /* On ne fait le traitement des information qu'après l'envoi et la validation du formulaire. Ce
        block sert à modifier la vente et rediriger l'admin vers l'index des ventes.
        */
        if ($form->isSubmitted() && $form->isValid()) {
            /* Lors de la vente, on doit faire la mise à jour du stock pour eviter le mal
            fonctionnement du magasin du à la non fiabilité des données. 
            */
            $storage = $sale->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() - $sale->getQuantity());

            /* On valide les contraintes posé dans l'entité Stockage afin d'assurer l'intégrité de données avant
            de proceder aux modifications au niveau de la base de données.
            */
            $errors = $validator->validate($storage);
            if (count($errors) === 0) {
                /* Si tout se passe bien et les données saisies ne pose pas de problème, on modifie le 
                stockage du produit converné */
                $sale->setOldQuantity($sale->getQuantity());

                // On calcule le nouveau total de la vente
                $sale->setTotal($sale->getProduct()->getPrice() * $sale->getQuantity());

                // Puis fait les modification nécéssaires dans la base de donnée 
                $this->getDoctrine()->getManager()->flush();
    
                // Redirection vers l'index des ventes
                return $this->redirectToRoute('app_sales');
            } else {
                /* Si on rencontre une erreur lors de la validation des contraintes, on fait une sorte de 
                rollback et puis on affiche l'erreur adéquate.
                */
                $storage->setQuantity($storage->getQuantity() + $sale->getQuantity());
                if ($storage->getQuantity() === 0) {
                    $form->get('quantity')->addError(new FormError('Product not available!'));
                } else {
                    $form->get('quantity')->addError(new FormError('Quantity not available. Storage only contains '.$storage->getQuantity().' units!'));
                }
            }
        }
        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'admin
        return $this->render('transactions/sale/edit.html.twig', [
            'sale' => $sale,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    /* Cette méthode est appelé lors d'un appel à la suppression d'une vente séléctionnée. La supression est
    réservée pour l'admin seulement afin de proteger les informations du magasin.
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}", name="app_sales_delete", methods={"POST"})
     */
    public function delete(Request $request, Sale $sale): Response
    {
        /* Pour des raison de sécurité, nous vérifions le jeton csrf si il est bien conforme au jeton de 
        notre formulaire ou il a été falsifié avant d'éffectuer la suppréssion de la vente.
        */
        if ($this->isCsrfTokenValid('delete'.$sale->getId(), $request->request->get('_token'))) {
            // On fait la mise à jour du stockage du produit de la vente convernée
            $storage = $sale->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() + $sale->getQuantity());

            // On supprime la vente et on fait les modifications nécessaires dans la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sale);
            $entityManager->flush();
        }

        // Redirection vers l'index des ventes
        return $this->redirectToRoute('app_sales');
    }
}
