<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Vérifie que l'utilisateur a au moins un restaurant avant d'accéder aux pages de création de menus.
 * Redirige vers la création de restaurant si nécessaire.
 */
class RestaurantOnboardingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TokenStorageInterface $tokenStorage
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Routes qui nécessitent un restaurant
        $protectedRoutes = [
            'ea_new', // EasyAdmin new action
        ];

        // Ne vérifier que sur les routes protégées
        if (!in_array($route, $protectedRoutes, true)) {
            return;
        }

        // Vérifier que c'est une action de création de menu (Ardoise)
        $crudController = $request->attributes->get('crudControllerFqcn');
        if (!in_array($crudController, [
            'App\Controller\Admin\DailyMenuCrudController',
            'App\Controller\Admin\SpecialMenuCrudController',
        ], true)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        /** @var User|null $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Vérifier si l'utilisateur a au moins un restaurant
        if ($user->getRestaurants()->isEmpty()) {
            // Rediriger vers la création de restaurant avec un message flash
            $request->getSession()->getFlashBag()->add(
                'warning',
                'Vous devez d\'abord créer un restaurant avant de pouvoir créer des menus.'
            );

            $redirectUrl = $this->urlGenerator->generate('ea_new', [
                'crudAction' => 'new',
                'crudControllerFqcn' => 'App\\Controller\\Admin\\RestaurantCrudController',
            ]);

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
