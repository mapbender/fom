<?php

namespace FOM\UserBundle\Form\Type;

use FOM\UserBundle\Form\EventListener\UserSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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

    public function getParent()
    {
        return 'FOM\UserBundle\Form\Type\UserPasswordMixinType';
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
        ;

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

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $user = $form->getData();
        $usernameEnabled = $options['group_permission'] || !$user->getId();
        $view['username']->vars['disabled'] = !$usernameEnabled;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'group_permission' => false,
            'acl_permission' => false,
        ));
    }
}
