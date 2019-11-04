<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author apour
 * @author Christian Wygoda
 */
class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,array $options)
    {
        $builder->add("username", 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
            'required' => true,
            'label' => 'fom.user.registration.form.username',
            'attr' => array(
                'autofocus' => 'on',
            ),
        ));

        $builder->add('password', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', array(
            'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
            'first_options' => array(
                'label' => 'fom.user.registration.form.choose_password',
            ),
            'second_options' => array(
                'label' => 'fom.user.registration.form.confirm_password',
            ),
            'invalid_message' => 'The password fields must match.',
        ));

        $builder->add("email", 'Symfony\Component\Form\Extension\Core\Type\EmailType', array(
            'required' => true,
            'label' => 'fom.user.registration.form.email',
        ));
    }
}
