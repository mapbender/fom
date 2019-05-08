<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserResetPassType extends AbstractType {
    public function getName() {
        return 'user';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array(
                    'required' => $options['requirePassword'],
                    'label' => 'Password')));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'requirePassword' => true
        ));
    }
}
