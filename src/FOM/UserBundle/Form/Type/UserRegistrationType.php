<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class UserRegistrationType
 * @package FOM\UserBundle\Form\Type
 */
class UserRegistrationType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return "User";
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder,array $options)
    {
        $builder
            ->add("username",TextType::class, array(
            "required" => true,
            'attr' => array(
                'autofocus' => 'on'
            )
        ))->add('password', RepeatedType::class, array(
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'options' => array('label' => 'Password'),
        ))->add("email",EmailType::class,array(
            "required" => true
        ));
    }
}

