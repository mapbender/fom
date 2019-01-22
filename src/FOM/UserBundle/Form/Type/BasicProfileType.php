<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOM\UserBundle\Entity\BasicProfile;

/**
 * Class BasicProfileType
 * @package FOM\UserBundle\Form\Type
 */
class BasicProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = BasicProfile::getOrganizationRoleChoices();

        $builder
            ->add('firstName', TextType::class, array(
                'required' => false))
            ->add('lastName', TextType::class, array(
                'required' => false))
            ->add('notes', TextType::class, array(
                'required' => false))
            ->add('phone', TextType::class, array(
                'required' => false))
            ->add('street', TextType::class, array(
                'required' => false))
            ->add('zipCode', TextType::class, array(
                'required' => false))
            ->add('city', TextType::class, array(
                'required' => false))
            ->add('country', TextType::class, array(
                'required' => false))
            ->add('organizationName', TextType::class, array(
                'required' => false))
            ->add('organizationRole', ChoiceType::class, array(
                'choices' => $roles,
                'empty_value' => 'Choose an option...',
                'required' => false));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FOM\UserBundle\Entity\BasicProfile',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'profile';
    }
}
