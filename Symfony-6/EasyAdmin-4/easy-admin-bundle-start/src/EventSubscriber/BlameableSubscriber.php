<?php

namespace App\EventSubscriber;

use App\Entity\Question;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\Security\Core\Security;

class BlameableSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security)
    {
    }

    public function onBeforeEntityUpdatedEvent(BeforeEntityUpdatedEvent $event)
    {
        $question = $event->getEntityInstance();
        if (!$question instanceof Question) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('The user must be an instance of ' . User::class);
        }

        $question->setUpdatedBy($user);
    }

    public static function getSubscribedEvents()
    {
        return [
            //  BeforeEntityUpdatedEvent::class => 'onBeforeEntityUpdatedEvent',
        ];
    }
}
