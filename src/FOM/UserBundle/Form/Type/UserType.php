<?php

namespace FOM\UserBundle\Form\Type;

use FOM\UserBundle\Form\EventListener\UserSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserType extends AbstractType
{
    /** @var string|null */
    protected $profileType;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param string|null $profileType
     */
    public function __construct(TokenStorageInterface $tokenStorage, $profileType)
    {
        $this->tokenStorage = $tokenStorage;
        $this->profileType = $profileType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new UserSubscriber($this->tokenStorage));
        $builder
            ->add('username', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
                'label' => 'fom.user.user.container.username',
                'attr' => array(
                    'autofocus' => true,
                ),
            ))
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', array(
                'label' => 'E-Mail',
            ))
            ->add('password', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', array(
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
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
        /**
         * @todo: password field can be permanently set to mapped = false once a DataTransforrmer ensuers
         *         non-empty model data. Mapping is currently required for creating new users (empty password property
         *         on User entity)
         */
        $builder->get('password')->setMapped($options['requirePassword']);
        $builder->get('username')->setDisabled(!$options['group_permission']);

        if (true === $options['group_permission']) {
            $builder
                ->add('groups', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                    'class' =>  'FOMUserBundle:Group',
                    'query_builder' => function (EntityRepository $er) {
                        $qb = $er->createQueryBuilder('r')
                            ->add('orderBy', 'r.title ASC');
                        return $qb;
                    },
                    'expanded' => true,
                    'multiple' => true,
                    'choice_label' => 'title',
                    // collection field rendering bypasses form theme; suppress
                    // the spurious label if collection is empty
                    'label_attr' => array(
                        'class' => 'hidden',
                    ),
                    'label' => 'fom.user.user.container.groups',
                ));
        }

        if ($options['acl_permission']) {
            $builder
                ->add('acl', 'FOM\UserBundle\Form\Type\ACLType', array(
                    'mapped' => false,
                    'data' => $options['data'],
                    'permissions' => 'standard::object',
                    'standard_anon_access' => false,
                ))
            ;
        }
        if ($this->profileType) {
            $builder->add('profile', $this->profileType, array(
                'label' => 'fom.user.user.container.profile',
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'requirePassword' => true,
            'group_permission' => false,
            'acl_permission' => false,
        ));
    }
}
