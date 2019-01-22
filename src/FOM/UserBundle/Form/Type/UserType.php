<?php

namespace FOM\UserBundle\Form\Type;

use FOM\UserBundle\Form\EventListener\UserSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class UserType
 * @package FOM\UserBundle\Form\Type
 */
class UserType extends AbstractType
{
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
        $builder->addEventSubscriber(new UserSubscriber($builder->getFormFactory(), $options['currentUser']));
        $builder
            ->add('username', TextType::class)
            ->add('email', EmailType::class, array(
                'label' => 'E-Mail'))
            ->add('password', RepeatedType::class, array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'required' => $options['requirePassword'],
                'options' => array(
                    'label' => 'Password')));

        if (true === $options['group_permission']) {
            $builder
                ->add('groups', EntityType::class, array(
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

        if (true === $options['acl_permission']) {
            $builder
                ->add('acl', ACLType::class, array(
                    'mapped' => false,
                    'data' => $options['data'],
                    'permissions' => 'standard::object',
                    'standard_anon_access' => false));
        }

        if ($options['profile_formtype']) {
            $formType = $options['profile_formtype'];
            $builder->add('profile', new $formType());
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'requirePassword' => true,
            'profile_formtype' => null,
            'group_permission' => false,
            'acl_permission' => false,
            'cascade_validation' => true,
            'currentUser' => null
        ));
    }
}
