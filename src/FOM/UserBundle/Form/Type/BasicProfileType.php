<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOM\UserBundle\Entity\BasicProfile;


class BasicProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = BasicProfile::getOrganizationRoleChoices();

        $builder
            ->add('firstName', 'text', array(
                'required' => false,
                'label' => 'First name'))
            ->add('lastName', 'text', array(
                'required' => false,
                'label' => 'Last name'))
            ->add('notes', 'text', array(
                'required' => false,
                'label' => 'Notes'))
            ->add('phone', 'text', array(
                'required' => false,
                'label' => 'Phone'))
            ->add('street', 'text', array(
                'required' => false,
                'label' => 'Street'))
            ->add('zipCode', 'text', array(
                'required' => false,
                'label' => 'Zip code'))
            ->add('city', 'text', array(
                'required' => false,
                'label' => 'City'))
            ->add('country', 'text', array(
                'required' => false,
                'label' => 'Country'))
            ->add('organizationName', 'text', array(
                'required' => false,
                'label' => 'Organization'))
            ->add('organizationRole', 'choice', array(
                'choices' => $roles,
                'empty_value' => 'Choose an option...',
                'required' => false,
                'label' => 'Role'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
