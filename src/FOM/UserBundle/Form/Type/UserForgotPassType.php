<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserForgotPassType extends AbstractType
{
    public function getName()
    {
        return 'passwordresetrequest';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', 'text', array(
                'label' => 'fom.user.password.form.username_email',
                'attr' => array(
                    'autofocus' => 'on',
                ),
            ))
        ;

    }
}

