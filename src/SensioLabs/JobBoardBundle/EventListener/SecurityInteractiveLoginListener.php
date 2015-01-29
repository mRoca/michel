<?php


namespace SensioLabs\JobBoardBundle\EventListener;

use Doctrine\ORM\EntityManager;
use SensioLabs\Connect\Security\Authentication\Token\ConnectToken;
use SensioLabs\JobBoardBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SecurityInteractiveLoginListener implements EventSubscriberInterface
{
    private $em;

    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        );
    }

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();

        if (!$token instanceof ConnectToken) {
            return;
        }

        /** @var User $user */
        $user = $token->getUser();
        $user->updateFromConnect($token->getApiUser());

        $this->em->persist($user);
        $this->em->flush($user);
    }
}
