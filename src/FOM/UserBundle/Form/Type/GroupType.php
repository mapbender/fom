<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupType extends AbstractType
{
    public function getName()
    {
        return 'group';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Name',
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label' => 'fom.user.user.container.description',
            ))
            ->add('users', EntityType::class, array(
                'class' =>  'FOMUserBundle:User',
                'expanded' => true,
                'multiple' => true,
                'property' => 'username',
                'label' => 'Users',
            ));
    }
}
