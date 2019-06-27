<?php
namespace FOM\UserBundle\Form\Type;

use FOM\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * ACL form type
 *
 * This type embeds a ACL configuration form which can be used to manage object
 * and class ACEs.
 *
 * Two sets of standard permissions are available to show for each ACE:
 * - "standard::object": Standard permissions for objects
 * - "standard::class": Standard permissions for classes
 * These can be provided using the 'permissions' parameter which can
 * alternatively also provided an array of permissions where keys are 1-30
 * (bitmask position) and the values the labels to show in the form.
 *
 * @author Christian Wygoda
 */
class ACLType extends AbstractType
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AclProviderInterface */
    protected $aclProvider;

    /**
     * ACLType constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param AclProviderInterface $aclProvider
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AclProviderInterface $aclProvider)
    {
        $this->tokenStorage = $tokenStorage;
        $this->aclProvider = $aclProvider;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'acl';
    }

    protected function loadAces($options)
    {
        if ($options['class'] && class_exists($options['class'])) {
            $oid = new ObjectIdentity('class', $options['class']);
            $acl = $this->aclProvider->findAcl($oid);
            return $acl->getClassAces();
        } else {
            $oid = ObjectIdentity::fromDomainObject($options['data']);
            $acl = $this->aclProvider->findAcl($oid);
            return $acl->getObjectAces();
        }
    }

    protected function buildAces($options)
    {
        $aces = array();
        if ($options['create_standard_permissions']) {
            // for unsaved entities, fake three standard permissions:
            // - Owner access for current user
            // - View access for anonymous users
            // - View access for logged in users
            $aces = array();

            /** @var User $owner */
            $owner = $this->tokenStorage->getToken()->getUser();
            $ownerAccess = array (
                'sid' => UserSecurityIdentity::fromAccount($owner),
                'mask' => MaskBuilder::MASK_OWNER);

            $aces[] = $ownerAccess;
        }
        if ($options['standard_anon_access'] || ($options['standard_anon_access'] === null && $options['create_standard_permissions'])) {
            $anon = new RoleSecurityIdentity('IS_AUTHENTICATED_ANONYMOUSLY');
            $aces[] = array(
                'sid' => $anon,
                'mask' => MaskBuilder::MASK_VIEW,
            );
        }
        return $aces;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        try {
            $aces = $this->loadAces($options);
        } catch (\Symfony\Component\Security\Acl\Exception\Exception $e) {
            $aces = $this->buildAces($options);
        }

        $permissions = is_string($options['permissions']) ? $this->getStandardPermissions($options['permissions']) : $options['permissions'];

        $aceOptions = array (
            'type' => 'ace',
            'label' => 'Permissions',
            'allow_add' => true,
            'allow_delete' => true,
            'auto_initialize' => false,
            'prototype' => true,
            'options' => array(
                'available_permissions' => $permissions,
            ),
            'mapped' => false,
            'data' => $aces,
        );

        $builder->add('ace', 'collection', $aceOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array (
            'permissions' => array (),
            'class' => null,
            'create_standard_permissions' => true,
            'standard_anon_access' => null,
            'user' => null,
            'force_master' => false,
            'force_owner' => false
        ));
    }

    /**
     * Get standard permission sets for provided string identifier.
     *
     * @param string $type
     * @return string[]
     */
    protected function getStandardPermissions($type)
    {
        switch ($type) {
            case 'standard::object':
            case 'standard::class':
                $permissions = array(
                    1 => 'View',
                    2 => 'Create',
                    3 => 'Edit',
                    4 => 'Delete',
                    6 => 'Operator',
                    7 => 'Master',
                    8 => 'Owner',
                );
                if ($type !== 'standard::class') {
                    // suppress redundant create permission on concrete objects
                    unset($permissions[2]);
                }
                return $permissions;
            default:
                throw new \RuntimeException(var_export($type, true) . ' is not a valid standard permission set identifier');
        }
    }
}
