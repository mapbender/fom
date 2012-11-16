<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
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
                'sid' => UserSecurityIdentity::fromAccount($owner),
                'mask' => MaskBuilder::MASK_OWNER);
            
            $anon = new RoleSecurityIdentity('IS_AUTHENTICATED_ANONYMOUSLY');
            $anonAccess = array(
                'sid' => $anon,
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
            1 => 'View',
            2 => 'Create',
            3 => 'Edit',
            4 => 'Delete',
            5 => 'Undelete',
            6 => 'Operator',
            7 => 'Master',
            8 => 'Owner'
        );

        $resolver->setDefaults(array(
            'available_permissions' => $availablePermissions,
            'user' => null,
            'exclude' => array()));
    }
}
