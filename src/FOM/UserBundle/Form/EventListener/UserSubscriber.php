<?php

namespace FOM\UserBundle\Form\EventListener;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use FOM\UserBundle\Entity\User;

/**
 *
 */
class UserSubscriber implements EventSubscriberInterface
{

    /**
     *
     * @var User
     */
    private $currentUser;

    /**
     * @inheritdoc
     */
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
        $user = $event->getData();
        if (null === $user) {
            return;
        }
        if ($this->currentUser !== null && $this->currentUser !== $user && $event->getForm()->has('activated')) {
            $activated = $event->getForm()->get('activated')->getData();
            if ($activated && $user->getRegistrationToken()) {
                $user->setRegistrationToken(null);
            } elseif (!$activated && !$user->getRegistrationToken()) {
                $user->setRegistrationToken(hash("sha1",rand()));
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $user = $event->getData();
        if (null === $user) {
            return;
        }
        if ($user->getId() && $this->currentUser !== null && $this->currentUser !== $user) {
            $event->getForm()->add('activated', CheckboxType::class, array(
                'data' => $user->getRegistrationToken() ? false : true,
                'auto_initialize' => false,
                'label' => 'fom.user.user.container.activated',
                'required' => false,
                'mapped' => false,
            ));
        }
    }
}
