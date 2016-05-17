<?php

namespace FOM\UserBundle\Component;

use FOM\UserBundle\Entity\AclEntry;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;

/**
 * ACL Manager service implementation
 *
 * This manager is available as a service called 'fom.acl.manager' and is meant
 * to be used with a form and will delete/update/add ACEs.
 *
 * @author     Christian Wygoda
 * @author     Andriy Oblivantsev
 * @deprecated Use <\Mapbender\CoreBundle\Component\AclManager> instead.
 */
/**
 * ACL Manager service implementation
 *
 * This manager is available as a service called 'mapbender.acl' and is meant
 * to be used with a form and will delete/update/add ACEs.
 *
 * @author Christian Wygoda
 * @author Andriy Oblivantsev
 */
class AclManager
{
    /** @var MutableAclProvider  */
    protected $aclProvider;

    /**
     * AclManager constructor.
     *
     * @param MutableAclProvider $aclProvider
     */
    public function __construct(MutableAclProvider $aclProvider)
    {
        $this->aclProvider = $aclProvider;
    }

    /**
     * Update ACEs from a form of class FOM\UserBundle\Form\Type\ACLType
     * (commonly 'acl')
     * @param object $entity Entity to update ACL for
     * @param object $form   ACLType form object
     * @param string $type   ACE type to update (object or class)
     */
    public function setObjectACLFromForm($entity, $form, $type)
    {
        $aces = $form->get('ace')->getData();
        $this->setObjectACL($entity, $aces, $type);
    }

    /**
     * Update ACEs for entity
     * @param object $entity Entity to update ACL for
     * @param array  $aces   Array with ACEs (not Entry objects!)
     * @param string $type   ACE type to update (object or class)
     */
    public function setObjectACL($entity, $aces, $type)
    {
        switch($type) {
            case 'object':
                $deleteMethod = 'deleteObjectAce';
                $insertMethod = 'insertObjectAce';
                break;
            default:
                throw new \RuntimeException('ACEs of type ' . $type
                    . ' are not supported.');
        }
        $acl = $this->getAcl($entity);
        $oldAces = $acl->getObjectAces();

        // @TODO: This is a naive implementation, we should update where
        // possible instead of deleting all old ones and adding all new ones...

        // Delete old ACEs
        foreach(array_reverse(array_keys($oldAces)) as $idx) {
            $acl->$deleteMethod(intval($idx));
        }
        $this->aclProvider->updateAcl($acl);
        // Add new ACEs
        foreach(array_reverse($aces) as $idx => $ace) {
            // If no mask is given, we might as well not insert the ACE
            if($ace['mask'] === 0) {
                continue;
            }
            $acl->$insertMethod($ace['sid'], $ace['mask']);
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Update ACEs from a form of class FOM\UserBundle\Form\Type\ACLType
     * (commonly 'acl')
     *
     * @param        $class
     * @param object $form ACLType form object
     * @internal param object $entity Entity to update ACL for
     * @internal param string $type ACE type to update (object or class)
     */
    public function setClassACLFromForm($class, $form)
    {
        $aces = $form->get('ace')->getData();
        $this->setClassACL($class, $aces);
    }

    /**
     * @param $class
     * @param $aces
     * @throws \Exception
     */
    protected function setClassACL($class, $aces)
    {
        $acl = $this->getAcl($class);
        $oldAces = $acl->getClassAces();

        // @TODO: This is a naive implementation, we should update where
        // possible instead of deleting all old ones and adding all new ones...

        // Delete old ACEs
        foreach(array_reverse(array_keys($oldAces)) as $idx) {
            $acl->deleteClassAce(intval($idx));
        }

        $this->aclProvider->updateAcl($acl);
        // Add new ACEs
        foreach(array_reverse($aces) as $idx => $ace) {
            // If no mask is given, we might as well not insert the ACE
            if($ace['mask'] === 0) {
                continue;
            }
            $acl->insertClassAce($ace['sid'], $ace['mask']);
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Get ACL object manager.
     *
     * @param $entity
     * @return mixed
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function getACL($entity)
    {
        if (is_string($entity) && class_exists($entity)) {
            $oid = new ObjectIdentity('class', $entity);
        } else {
            $oid = ObjectIdentity::fromDomainObject($entity);
        }

        try {
            $acl = $this->aclProvider->findAcl($oid);
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl($oid);
        }

        return $acl;
    }

    /**
     * Get object ACL entries
     *
     * @param $entity
     * @return Entry[]
     */
    public function getObjectAclEntries($entity)
    {
        /** @var Acl $acl */
        $acl = $this->getACL($entity);
        return $acl->getObjectAces();
    }

    /**
     * Get true if object has some ACL entries
     *
     * @param $entity
     * @return bool
     */
    public function hasObjectAclEntries($entity)
    {
        return count($this->getObjectAclEntries($entity)) > 0;
    }


    /**
     * Consolidate object ACL entries to an <AclEntry> array.
     *
     * @param $entity
     * @return AclEntry[]
     */
    public function getObjectAclEntriesAsArray($entity)
    {
        $result = array();
        foreach ($this->getObjectAclEntries($entity) as $aclEntry) {
            $result[] = new AclEntry($aclEntry->getSecurityIdentity());
        }
        return $result;
    }
}
