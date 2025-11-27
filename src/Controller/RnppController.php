<?php

namespace App\Controller;

use App\Entity\Rnpp;
use App\Form\RnppForm;
use App\Repository\RnppRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rnpp')]
final class RnppController extends AbstractController
{
    #[Route(name: 'app_rnpp_index', methods: ['GET'])]
    public function index(RnppRepository $rnppRepository): Response
    {
        return $this->render('rnpp/index.html.twig', [
            'rnpps' => $rnppRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_rnpp_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rnpp = new Rnpp();
        $form = $this->createForm(RnppForm::class, $rnpp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rnpp);
            $entityManager->flush();

            return $this->redirectToRoute('app_rnpp_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rnpp/new.html.twig', [
            'rnpp' => $rnpp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rnpp_show', methods: ['GET'])]
    public function show(Rnpp $rnpp): Response
    {
        return $this->render('rnpp/show.html.twig', [
            'rnpp' => $rnpp,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rnpp_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rnpp $rnpp, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RnppForm::class, $rnpp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rnpp_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rnpp/edit.html.twig', [
            'rnpp' => $rnpp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rnpp_delete', methods: ['POST'])]
    public function delete(Request $request, Rnpp $rnpp, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rnpp->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rnpp);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rnpp_index', [], Response::HTTP_SEE_OTHER);
    }

}
