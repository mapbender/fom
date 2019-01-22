<?php

namespace FOM\UserBundle\Form\Type;

use Mapbender\CoreBundle\Component\SecurityContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\DependencyInjection\Container;
use FOM\ManagerBundle\Form\Type\TagboxType;
use FOM\UserBundle\Form\DataTransformer\ACEDataTransformer;

/**
 * Class ACEType
 * @package FOM\UserBundle\Form\Type
 */
class ACEType extends AbstractType
{
    /** @var SecurityContext  */
    protected $securityContext;

    /** @var AclProviderInterface  */
    protected $aclProvider;

    /** @var Container  */
    protected $container;

    /**
     * ACEType constructor.
     * @param SecurityContext $securityContext
     * @param AclProviderInterface $aclProvider
     * @param Container $container
     */
    public function __construct(SecurityContext $securityContext,
        AclProviderInterface $aclProvider,
        Container $container)
    {
        $this->securityContext = $securityContext;
        $this->aclProvider = $aclProvider;
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ace';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ACEDataTransformer($this->container);
        $builder->addModelTransformer($transformer);

        $builder->add('sid', TextType::class, array(
            'required' => true,
            'label' => 'Role or user',
            'attr' => array(
                'data-provide' => 'typeahead',
                'autocomplete' => 'off')));

        $permissions = $options['available_permissions'];

        foreach ($permissions as $bit => $perm){
            $name = strtolower($perm);
            $builder->add('permission_' . $bit, TagboxType::class, array(
                'property_path' => '[permissions][' . $bit . ']',
                'attr' => array("class"=>$name)));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'available_permissions' => array()));
    }
}
