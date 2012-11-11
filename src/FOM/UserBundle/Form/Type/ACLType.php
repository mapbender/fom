<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;

class ACLType extends AbstractType
{
    protected $securityContext;
    protected $aclProvider;

    public function __construct(SecurityContext $securityContext,
        AclProviderInterface $aclProvider)
    {
        $this->securityContext = $securityContext;
        $this->aclProvider = $aclProvider;
    }

    public function getName()
    {
        return 'acl';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        try {
            $oid = ObjectIdentity::fromDomainObject($options['data']);
            $acl = $this->aclProvider->findAcl($oid);
            $aces = $acl->getObjectAces();
        } catch(InvalidDomainObjectException $e) {
            $oid = null;
            $aces = array();

            $owner = $this->securityContext->getToken()->getUser();
            $ownerAccess = array(
                'securityIdentity' => UserSecurityIdentity::fromAccount($owner),
                'mask' => MaskBuilder::MASK_OWNER);
            
            $anon = new RoleSecurityIdentity('IS_AUTHENTICATED_ANONYMOUSLY');
            $anonAccess = array(
                'securityIdentity' => $anon,
                'mask' => MaskBuilder::MASK_VIEW);

            $aces[] = $ownerAccess;
            $aces[] = $anonAccess;
        }
        
        
        $builder->add('ace', 'collection', array(
            'type' => 'ace',
            'options' => array(
                'required' => false,
                //is_master
                //is_owner
                'available_permissions' => $options['available_permissions']),
            'property_path' => false,
            'data' => $aces));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $availablePermissions = array(
            1 << 0 => 'View',
            1 << 1 => 'Create',
            1 << 2 => 'Edit',
            1 << 3 => 'Delete',
            1 << 4 => 'Undelete',
            1 << 5 => 'Operator',
            1 << 6 => 'Master',
            1 << 7 => 'Owner'
        );

        $resolver->setDefaults(array(
            'available_permissions' => $availablePermissions,
            'user' => null,
            'exclude' => array()));
    }
}
