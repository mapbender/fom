<?php

namespace FOM\CoreBundle\Form\Type;

use FOM\CoreBundle\Form\DataTransformer\GroupIdTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class FOMGroupsType extends AbstractType
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;


    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * FOMGroupsType constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fom_groups';
    }

    /**
     * @return string|\Symfony\Component\Form\FormTypeInterface|null
     */
    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $type = $this;
        $resolver->setDefaults(array(
            'user_groups' => false,
            'return_entity' => false,
            'compound' => false,
            'class' => 'FOMUserBundle:Group',
            'property' => 'title',
            'query_builder' => function(Options $options) use ($type) {
                $builderName = preg_replace("/[^\w]/", "", $options['property_path']);
                $repository = $type->entityManager->getRepository("FOMUserBundle:Group");
                $qb = $repository->createQueryBuilder($builderName);

                if($options['user_groups']) {
                    $user = $type->tokenStorage->getToken()->getUser();
                    if(is_object($user)) {
                        $qb->join($builderName . '.users', 'u', 'WITH', 'u.id = :uid')
                            ->setParameter('uid', $user->getId());
                    }
                }
                return $qb;
            }
        ));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['return_entity'] === false) {
            $transformer = new GroupIdTransformer($this->entityManager);
            $builder->addModelTransformer($transformer);
        }
    }
}
