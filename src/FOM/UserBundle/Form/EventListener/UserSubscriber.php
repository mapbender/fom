<?php

namespace FOM\UserBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use FOM\UserBundle\Entity\User;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var User|null
     */
    private $currentUser;

    public function __construct(User $currentUser = null)
    {
        $this->currentUser = $currentUser;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'submit',
        );
    }


    /**
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        /** @var User|null $user */
        $user = $event->getData();
        if (null === $user) {
            return;
        }
        if (!$event->getForm()->get('activated')->isDisabled()) {
            $activated = $event->getForm()->get('activated')->getData();
            if ($activated) {
                $user->setRegistrationToken(null);
            } elseif (!$user->getRegistrationToken()) {
                $user->setRegistrationToken(hash("sha1",rand()));
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var User|null $user */
        $user = $event->getData();
        if (null === $user) {
            return;
        }
        $event->getForm()->add('activated', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
            'data' => $user->getRegistrationToken() ? false : true,
            'label' => 'fom.user.user.container.activated',
            'required' => false,
            'mapped' => false,
            'disabled' => ($this->currentUser && $this->currentUser === $user),
        ));
    }
}
