<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class UserResetPassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'first_options' => array(
                    'label' => 'fom.user.registration.form.choose_password',
                ),
                'second_options' => array(
                    'label' => 'fom.user.registration.form.confirm_password',
                ),
            ))
        ;
    }
}
