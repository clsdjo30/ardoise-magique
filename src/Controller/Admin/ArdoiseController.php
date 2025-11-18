<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Form\ArdoiseType;
use App\Repository\ArdoiseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ardoise')]
#[IsGranted('ROLE_USER')]
class ArdoiseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArdoiseRepository $ardoiseRepository
    ) {
    }

    #[Route('/new', name: 'app_admin_ardoise_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $ardoise = new Ardoise();
        $ardoise->setRestaurateur($this->getUser());

        $form = $this->createForm(ArdoiseType::class, $ardoise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($ardoise);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'ardoise a été créée avec succès !');

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/ardoise/new.html.twig', [
            'ardoise' => $ardoise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_ardoise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ardoise $ardoise): Response
    {
        // Vérification de propriété
        if ($ardoise->getRestaurateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas éditer cette ardoise.');
        }

        $form = $this->createForm(ArdoiseType::class, $ardoise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'ardoise a été modifiée avec succès !');

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/ardoise/edit.html.twig', [
            'ardoise' => $ardoise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-active', name: 'app_admin_ardoise_toggle_active', methods: ['POST'])]
    public function toggleActive(Request $request, Ardoise $ardoise): Response
    {
        // Vérification de propriété
        if ($ardoise->getRestaurateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas activer cette ardoise.');
        }

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('toggle-active-' . $ardoise->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Désactiver toutes les ardoises de l'utilisateur
        $user = $this->getUser();
        foreach ($user->getArdoises() as $a) {
            $a->setIsActive(false);
        }

        // Activer l'ardoise sélectionnée
        $ardoise->setIsActive(true);

        $this->entityManager->flush();

        $this->addFlash('success', sprintf('L\'ardoise "%s" est maintenant active !', $ardoise->getTitre()));

        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/{id}', name: 'app_admin_ardoise_delete', methods: ['POST'])]
    public function delete(Request $request, Ardoise $ardoise): Response
    {
        // Vérification de propriété
        if ($ardoise->getRestaurateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette ardoise.');
        }

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('delete-' . $ardoise->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $titre = $ardoise->getTitre();

        $this->entityManager->remove($ardoise);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('L\'ardoise "%s" a été supprimée.', $titre));

        return $this->redirectToRoute('app_admin_dashboard');
    }
}
