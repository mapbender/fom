<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class GroupType
 * @package FOM\UserBundle\Form\Type
 */
class GroupType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'group';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Name'))
            ->add('description', TextareaType::class, array('required' => false))
            ->add('users', EntityType::class, array(
                'class' =>  'FOMUserBundle:User',
                'expanded' => true,
                'multiple' => true,
                'property' => 'username',
                'label' => 'Users'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'available_roles' => array()
        ));
    }
}
