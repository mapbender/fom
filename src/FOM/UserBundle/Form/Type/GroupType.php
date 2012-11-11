<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'label' => 'Group title'))
            ->add('description', 'textarea', array(
                'label' => 'Group description'))
            ->add('users', 'entity', array(
                'class' =>  'FOMUserBundle:User',
                'expanded' => true,
                'multiple' => true,
                'property' => 'username',
                'label' => 'Users'))
            ->add('roles', 'choice', array(
                'expanded' => true,
                'multiple' => true,
                'choices' => $options['available_roles'],
                'label' => 'Roles'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'available_roles' => array()
        ));
    }
}
