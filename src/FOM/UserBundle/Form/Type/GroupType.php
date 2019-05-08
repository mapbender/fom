<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function getName()
    {
        return 'group';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'label' => 'Name'))
            ->add('description', 'textarea', array('required' => false))
            ->add('users', 'entity', array(
                'class' =>  'FOMUserBundle:User',
                'expanded' => true,
                'multiple' => true,
                'property' => 'username',
                'label' => 'Users'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'available_roles' => array()
        ));
    }
}
