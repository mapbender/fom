<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FOM\UserBundle\Entity\BasicProfile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = BasicProfile::getOrganizationRoleChoices();

        $builder
            ->add('firstName', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.firstname',
            ))
            ->add('lastName', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.lastName',
            ))
            ->add('notes', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.notes',
            ))
            ->add('phone', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.phone',
            ))
            ->add('street', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.street',
            ))
            ->add('zipCode', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.zipCode',
            ))
            ->add('city', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.city',
            ))
            ->add('country', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.country',
            ))
            ->add('organizationName', 'text', array(
                'required' => false,
                'label' => 'form.profile.basic.organizationName',
            ))
            ->add('organizationRole', 'choice', array(
                'choices' => $roles,
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
