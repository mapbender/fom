<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use FOM\UserBundle\Entity\BasicProfile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = BasicProfile::getOrganizationRoleChoices();

        $builder
            ->add('firstName', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.firstname',
            ))
            ->add('lastName', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.lastName',
            ))
            ->add('notes', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.notes',
            ))
            ->add('phone', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.phone',
            ))
            ->add('street', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.street',
            ))
            ->add('zipCode', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.zipCode',
            ))
            ->add('city', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.city',
            ))
            ->add('country', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.country',
            ))
            ->add('organizationName', TextType::class, array(
                'required' => false,
                'label' => 'form.profile.basic.organizationName',
            ))
            ->add('organizationRole', ChoiceType::class, array(
                'choices' => array_flip($roles),
                'choices_as_values' => true,
                'empty_value' => 'Choose an option...',
                'required' => false,
                'label' => 'form.profile.basic.organizationRole',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FOM\UserBundle\Entity\BasicProfile',
        ));
    }

    public function getName()
    {
        return 'profile';
    }
}
