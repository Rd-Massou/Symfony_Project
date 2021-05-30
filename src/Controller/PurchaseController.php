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
        return $this->render('transactions/purchase/index.html.twig', [
            'purchases' => $purchaseRepository->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/new", name="app_purchases_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $purchase = new Purchase();
        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchase->setTotal($purchase->getProduct()->getPrice() * $purchase->getQuantity());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($purchase);
            $entityManager->flush();

            return $this->redirectToRoute('app_purchases');
        }

        return $this->render('transactions/purchase/new.html.twig', [
            'purchase' => $purchase,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_purchases_show", methods={"GET"})
     */
    public function show(Purchase $purchase): Response
    {
        return $this->render('transactions/purchase/show.html.twig', [
            'purchase' => $purchase,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * isGranted("ROLE_ADMIN")
     * @Route("/{id}/edit", name="app_purchases_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Purchase $purchase): Response
    {
        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchase->setTotal($purchase->getProduct()->getPrice() * $purchase->getQuantity());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_purchases');
        }

        return $this->render('transactions/purchase/edit.html.twig', [
            'purchase' => $purchase,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    /**
     * isGranted("ROLE_ADMIN")
     * @Route("/{id}", name="app_purchases_delete", methods={"POST"})
     */
    public function delete(Request $request, Purchase $purchase): Response
    {
        if ($this->isCsrfTokenValid('delete'.$purchase->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($purchase);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_purchases');
    }
}
