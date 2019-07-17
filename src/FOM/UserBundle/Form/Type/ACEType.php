<?php
namespace FOM\UserBundle\Form\Type;

use FOM\UserBundle\Component\Ldap;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use FOM\ManagerBundle\Form\Type\TagboxType;

use FOM\UserBundle\Form\DataTransformer\ACEDataTransformer;

class ACEType extends AbstractType
{
    /** @var AclProviderInterface  */
    protected $aclProvider;

    protected $ldapUserProvider;

    /**
     * ACEType constructor.
     * @param AclProviderInterface $aclProvider
     * @param Ldap\UserProvider $ldapUserProvider
     */
    public function __construct(AclProviderInterface $aclProvider, Ldap\UserProvider $ldapUserProvider)
    {
        $this->aclProvider = $aclProvider;
        $this->ldapUserProvider = $ldapUserProvider;
    }

    public function getName()
    {
        return 'ace';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ACEDataTransformer($this->ldapUserProvider);
        $builder->addModelTransformer($transformer);

        $builder->add('sid', 'text', array(
            'required' => true,
            'label' => 'Role or user',
            'attr' => array(
                'autocomplete' => 'off',
                'readonly' => true,
            ),
        ));

        $permissions = $options['available_permissions'];

        foreach ($permissions as $bit => $perm){
            $name = strtolower($perm);
            $builder->add('permission_' . $bit, new TagboxType(), array(
                'property_path' => '[permissions][' . $bit . ']',
                'attr' => array("class"=>$name)));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'available_permissions' => array(),
        ));
    }
}
