<?php

namespace App\EventSubscriber;

use App\Entity\Ardoise;
use App\Entity\User;
use App\Service\Subscription\FeatureAccessService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class QuotaEnforcementSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FeatureAccessService $featureAccess,
        private RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['onBeforeEntityPersisted', 0],
        ];
    }

    public function onBeforeEntityPersisted(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        // Only handle Ardoise entities
        if (!$entity instanceof Ardoise) {
            return;
        }

        /** @var User|null $user */
        $user = $entity->getOwner();

        if (!$user) {
            return;
        }

        // Determine feature based on menu type
        $feature = $entity->getType() === Ardoise::TYPE_DAILY
            ? 'menus_daily'
            : 'menus_special';

        // First check if feature is accessible at all
        if (!$this->featureAccess->canAccessFeature($user, $feature)) {
            $message = $this->featureAccess->getUpgradeMessage($user, $feature);

            if ($message === null) {
                $message = sprintf(
                    'Cette fonctionnalité n\'est pas disponible avec votre plan actuel (%s).',
                    $this->featureAccess->getPlanName($user)
                );
            }

            $this->addFlashMessage('error', $message);

            throw new AccessDeniedHttpException($message);
        }

        // Then check quota
        if ($this->featureAccess->hasReachedQuota($user, $feature)) {
            $remaining = $this->featureAccess->getQuotaRemaining($user, $feature);
            $upgradeMessage = $this->featureAccess->getUpgradeMessage($user, $feature);

            $message = sprintf(
                'Vous avez atteint votre quota pour cette fonctionnalité (%d/%d utilisés). %s',
                $remaining !== null ? $remaining : 0,
                $remaining !== null ? $remaining : 0,
                $upgradeMessage ?? 'Passez à un plan supérieur pour continuer.'
            );

            $this->addFlashMessage('error', $message);

            throw new AccessDeniedHttpException($message);
        }
    }

    private function addFlashMessage(string $type, string $message): void
    {
        $session = $this->requestStack->getSession();

        // Add flash message if session supports FlashBag
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
