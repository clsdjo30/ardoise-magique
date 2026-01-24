<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Bloque l'accès au dashboard si l'utilisateur n'a pas de restaurant.
 * Redirige vers la page de création de restaurant.
 */
class DashboardAccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TokenStorageInterface $tokenStorage
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Routes du dashboard à protéger
        $protectedRoutes = [
            'app_admin_dashboard',
            'admin',
        ];

        // Ne vérifier que sur les routes protégées
        if (!in_array($route, $protectedRoutes, true)) {
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

        // Ne pas bloquer les super admins
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return;
        }

        // Vérifier si l'utilisateur a au moins un restaurant
        if ($user->getRestaurants()->isEmpty()) {
            // Rediriger vers la création de restaurant avec un message
            $session = $request->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add(
                    'info',
                    sprintf(
                        'Bienvenue %s ! Pour accéder à votre espace de gestion, veuillez d\'abord créer votre restaurant.',
                        $user->getFirstname()
                    )
                );
            }

            $redirectUrl = $this->urlGenerator->generate('app_onboarding_restaurant');
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
