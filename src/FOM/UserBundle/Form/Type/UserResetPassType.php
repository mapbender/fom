<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class UserResetPassType
 * @package FOM\UserBundle\Form\Type
 */
class UserResetPassType extends AbstractType {
    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array(
                    'required' => $options['requirePassword'],
                    'label' => 'Password')));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'requirePassword' => true
        ));
    }
}
