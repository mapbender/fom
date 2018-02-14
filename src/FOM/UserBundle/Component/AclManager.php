<?php

namespace FOM\UserBundle\Component;

use FOM\UserBundle\Entity\AclEntry;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;

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
    /** @var AclProviderInterface  */
    protected $aclProvider;

    /**
     * AclManager constructor.
     *
     * @param MutableAclProvider $aclProvider
     */
    public function __construct(AclProviderInterface $aclProvider)
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
     * @param string $type ACE type to update (object or class)
     */
    public function setObjectACL($entity, $aces, $type)
    {
        if ($type != "object") {
            throw new \RuntimeException('ACEs of type ' . $type . ' are not supported.');
        }

        $acl     = $this->getAcl($entity);
        $oldAces = $acl->getObjectAces();

        // Delete old ACEs
        foreach (array_reverse(array_keys($oldAces)) as $idx) {
            $acl->deleteObjectAce(intval($idx));
        }
        $this->aclProvider->updateAcl($acl);
        // Add new ACEs
        foreach (array_reverse($aces) as $idx => $ace) {
            // If no mask is given, we might as well not insert the ACE
            if ($ace['mask'] === 0) {
                continue;
            }
            $acl->insertObjectAce($ace['sid'], $ace['mask']);
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
        $acl     = $this->getAcl($class);
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
     * @param      $entity
     * @param bool $create
     * @return Acl | null
     * @throws \Exception
     * @throws \Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException
     */
    public function getACL($entity, $create = true)
    {
        $acl = null;
        $oid = $this->getEntityObjectId($entity);
        try {
            $acl = $this->aclProvider->findAcl($oid);
        } catch (NotAllAclsFoundException $e) {
            $acl = $e->getPartialResult();
        } catch (AclNotFoundException $e) {
            if($create){
                $acl = $this->aclProvider->createAcl($oid);
            }
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
        return $this->getACL($entity)->getObjectAces();
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

    /**
     * @param $entity
     * @return \Symfony\Component\Security\Acl\Domain\ObjectIdentity
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function getEntityObjectId($entity)
    {
        if (is_string($entity) && class_exists($entity)) {
            $oid = new ObjectIdentity('class', $entity);
        } else {
            $oid = ObjectIdentity::fromDomainObject($entity);
        }
        return $oid;
    }
}
