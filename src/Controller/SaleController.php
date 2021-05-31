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
        $sale = new Sale();
        $form = $this->createForm(SaleType::class, $sale);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $storage = $sale->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() - $sale->getQuantity());
            $errors = $validator->validate($storage);
            if (count($errors) === 0) {
                $sale->setOldQuantity($sale->getQuantity());
                $sale->setTotal($sale->getProduct()->getPrice() * $sale->getQuantity());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($sale);
                $entityManager->flush();
    
                return $this->redirectToRoute('app_sales');
            } else {
                $storage->setQuantity($storage->getQuantity() + $sale->getQuantity());
                if ($storage->getQuantity() === 0) {
                    $form->get('quantity')->addError(new FormError('Product not available!'));
                } else {
                    $form->get('quantity')->addError(new FormError('Quantity not available. Storage only contains '.$storage->getQuantity().' units!'));
                }
            }
        }

        return $this->render('transactions/sale/new.html.twig', [
            'sale' => $sale,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_sales_show", methods={"GET"})
     */
    public function show(Sale $sale): Response
    {
        return $this->render('transactions/sale/show.html.twig', [
            'sale' => $sale,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}/edit", name="app_sales_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Sale $sale, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(SaleType::class, $sale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storage = $sale->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() + $sale->getOldQuantity() - $sale->getQuantity());
            $this->oldQuantity = $sale->getQuantity();
            $sale->setTotal($sale->getProduct()->getPrice() * $sale->getQuantity());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_sales');
        }

        return $this->render('transactions/sale/edit.html.twig', [
            'sale' => $sale,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @isGranted("ROLE_ADMIN")
     * @Route("/{id}", name="app_sales_delete", methods={"POST"})
     */
    public function delete(Request $request, Sale $sale): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sale->getId(), $request->request->get('_token'))) {
            $storage = $sale->getProduct()->getStorage();
            $storage->setQuantity($storage->getQuantity() + $sale->getQuantity());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sale);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sales');
    }
}
