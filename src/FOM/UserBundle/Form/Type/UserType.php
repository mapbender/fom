<?php

namespace FOM\UserBundle\Form\Type;

use FOM\UserBundle\Form\EventListener\UserSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function getName()
    {
        return 'user';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new UserSubscriber($options['currentUser']));
        $builder
            ->add('username', 'text', array(
                'label' => 'fom.user.user.container.username',
                'attr' => array(
                    'autofocus' => true,
                ),
            ))
            ->add('email', 'email', array(
                'label' => 'E-Mail',
            ))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'required' => $options['requirePassword'],
                'mapped' => false,
                'first_options' => array(
                    'label' => 'fom.user.user.container.choose_password',
                ),
                'second_options' => array(
                    'label' => 'fom.user.user.container.confirm_password',
                ),
            ))
        ;
        $builder->get('password')->setMapped($options['requirePassword']);
        $builder->get('username')->setDisabled(!$options['group_permission']);

        if (true === $options['group_permission']) {
            $builder
                ->add('groups', 'entity', array(
                    'class' =>  'FOMUserBundle:Group',
                    'query_builder' => function (EntityRepository $er) {
                        $qb = $er->createQueryBuilder('r')
                            ->add('orderBy', 'r.title ASC');
                        return $qb;
                    },
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'title',
                    'label' => 'Groups'));
        }

        if ($options['acl_permission']) {
            $builder
                ->add('acl', 'acl', array(
                    'mapped' => false,
                    'data' => $options['data'],
                    'permissions' => 'standard::object',
                    'standard_anon_access' => false,
                ))
            ;
        }

        if ($options['profile_formtype']) {
            $builder->add('profile', $options['profile_formtype']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'requirePassword' => true,
            'profile_formtype' => null,
            'group_permission' => false,
            'acl_permission' => false,
            // @deprecated remove in FOM v3.3 (no longer valid in Symfony 3)
            'cascade_validation' => true,
            'currentUser' => null
        ));
    }
}
