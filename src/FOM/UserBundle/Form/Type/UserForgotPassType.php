<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class UserForgotPassType
 * @package FOM\UserBundle\Form\Type
 */
class UserForgotPassType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'passwordresetrequest';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, array(
                'label' => 'Username or Email',
                'attr' => array(
                    'autofocus' => 'on')));

    }
}

