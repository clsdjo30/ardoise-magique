<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\RestaurantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class OnboardingController extends AbstractController
{
    #[Route('/onboarding/restaurant', name: 'app_onboarding_restaurant')]
    public function createRestaurant(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Si l'utilisateur a déjà un restaurant, rediriger vers le dashboard
        if (!$user->getRestaurants()->isEmpty()) {
            return $this->redirectToRoute('admin', ['restaurant' => $user->getSlug()]);
        }

        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurant->setOwner($user);

            // Générer le slug de l'utilisateur basé sur le nom du restaurant
            if (!$user->getSlug() || str_contains($user->getSlug(), '@')) {
                $slugger = new AsciiSlugger();
                $user->setSlug($slugger->slug($restaurant->getName())->lower()->toString());
            }

            $entityManager->persist($restaurant);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Félicitations %s ! Votre restaurant "%s" a été créé avec succès.',
                $user->getFirstname(),
                $restaurant->getName()
            ));

            // Rediriger vers le dashboard
            return $this->redirectToRoute('admin', ['restaurant' => $user->getSlug()]);
        }

        return $this->render('onboarding/create_restaurant.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
