<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

/* Contrairement à HomeController, ici on définit la route du controlleur entièrement. Par la suite,
nous seront en mesure de prendre la meme route et composer la route de chacune des méthodes. On peut 
voir cette route comme un radical 
*/

/**
 * @Route("/products")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="app_products", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        /* On retourn la page index des produits ou nous listant tout les produits que le magisin vends,
        ceux-ci sont récupéré de la base données en utilisant le repository de l'entité produit et sa méthode
        findAll.
        */
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
            'user' => $this->getUser()
        ]);
    }

    /* Lors de la création d'un nouveau produit, cette route est demandé. L'autorisation est défini que pour 
    l'admin seul.
    */
    
    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/new", name="app_products_new", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        /* On génère une instance de l'entité produit et on appelle le formulaire relatif à celui-ci */
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        /* Puisque le formulaire envoi une requête POST pour pouvoir envoyer les données saisies par l'admin,
        on les traite par cette ligne
        */
        $form->handleRequest($request);

        /* On ne fait le traitement des information qu'après l'envoi et la validation du formulaire. Ce
        block sert à inserer le nouveau produit et rediriger l'admin au formulaire de création du stockage
        relatif au produit créé.
        */
        if ($form->isSubmitted() && $form->isValid()) {
            $productImage = $form->get('image')->getData();
            
            // Traiter l'image du produit si elle existe
            if ($productImage) {
                $originalImageName = pathinfo($productImage->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeImageName = $slugger->slug($originalImageName);
                $newImageName = $safeImageName . '-' . uniqid() . '.' . $productImage->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $productImage->move(
                        $this->getParameter('uploads_directory'),
                        $newImageName
                    );
                } catch (FileException $e) {
                }
                $product->setImage($newImageName);
            }

            // Inserer le produit dans la base donnée 
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            // Redirection vers le formulaire de stockage
            return $this->redirectToRoute('app_storage_new');
        }

        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'admin
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'user' => $this->getUser()
        ]);
    }

    // Cette méthode sert à visualiser un produit séléctionné.

    /**
     * @Route("/{id}", name="app_products_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        /* On retourn la page de details du produit séléctionné. Celui-ci est récupéré de la base données en
        utilisant l'id spécifié dans la route sans appeler le repository explicitement. Et on le passe comme
        parametre à twig
        */
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'user' => $this->getUser()
        ]);
    }

    /* Cette méthode sert à modifier un produit séléctionné. Elle renvoi le formulaire relatif au produit
    pré-rempli par les données deja existant. La modification du produit est résérvé seulement pour l'admin!
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}/edit", name="app_products_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Une fois les modifications faite et soumise, nous enregistons les celle-ci dans la base 
            de données
            */
            $productImage = $form->get('image')->getData();
            if ($productImage) {
                $originalImageName = pathinfo($productImage->getClientOriginalName(), PATHINFO_FILENAME);
                /* Ceci est nécessaire pour inclure en toute sécurité le nom du fichier dans l'URL puisque
                il ne faut jamais faire confiance à l'input de l'utilisateur. Il peux entrainer en danger pour
                notre serveur ou base de données.
                */
                $safeImageName = $slugger->slug($originalImageName);
                $newImageName = $safeImageName . '-' . uniqid() . '.' . $productImage->guessExtension();

                // On déplace l'image que l'utilisateur à soumis vers le dossier résévé aux uploads
                try {
                    $productImage->move(
                        $this->getParameter('uploads_directory'),
                        $newImageName
                    );
                } catch (FileException $e) {
                }
                $product->setImage($newImageName);
            }
            $this->getDoctrine()->getManager()->flush();

            // Redirection vers l'index de produits
            return $this->redirectToRoute('app_products');
        }

        // Afficher le formulaire une fois sur la page et attendre l'action effectué par l'utilisateur
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'user' => $this->getUser()
        ]);
    }

    /* Cette méthode est appelé lors d'un appel à la suppression d'un produit séléctionné. La supression est
    réservée pour l'admin seulement afin de proteger les informations du magasin.
    */

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}", name="app_products_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product): Response
    {
        /* Pour des raison de sécurité, nous vérifions le jeton csrf si il est bien conforme au jeton de 
        notre formulaire ou il a été falsifié avant d'éffectuer la suppression du produit.
        */
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            /* On gère un peux la mémoire au niveau du serveur en supprimant l'image relative au produit de
            notre dossier d'uploads puisque il ne sert plus à rien de le garder.
            */
            $filesystem = new Filesystem();
            try {
                $filesystem->remove($this->getParameter('uploads_directory').'/'.$product->getImage());
            } catch (IOExceptionInterface $exception) {
            }

            // Finalement on supprime le produit de la base de données en toute sécurité
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        // Redirection vers l'index de produits
        return $this->redirectToRoute('app_products');
    }
}
