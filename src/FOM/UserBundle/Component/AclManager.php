<?php

namespace FOM\UserBundle\Component;

use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * ACL utility service; registered as 'fom.acl.manager'
 *
 * This manager is available as a service called 'fom.acl.manager' and is meant
 * to be used with a form and will delete/update/add ACEs.
 *
 * @author     Christian Wygoda
 * @author     Andriy Oblivantsev
 */
class AclManager
{
    /** @var MutableAclProviderInterface */
    protected $aclProvider;

    /**
     * AclManager constructor.
     *
     * @param MutableAclProviderInterface $aclProvider
     */
    public function __construct(MutableAclProviderInterface $aclProvider)
    {
        $this->aclProvider = $aclProvider;
    }

    /**
     * Update ACEs for entity
     * @param object $entity Entity to update ACL for
     * @param array $aces   Array with ACEs (not Entry objects!)
     * @param mixed $ignored
     * @deprecated for misleading naming, use setObjectACEs; remove in FOM v3.3
     */
    public function setObjectACL($entity, $aces, $ignored = null)
    {
        $this->setObjectACEs($entity, $aces);
    }

    /**
     * Replace entity ACEs
     * @param object $entity
     * @param array $aces
     * @throws \Exception
     */
    public function setObjectACEs($entity, $aces)
    {
        $acl = $this->getAcl($entity);
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
     * @param $class
     * @param array[] $aces
     * @throws \Exception
     */
    public function setClassACEs($class, $aces)
    {
        $acl     = $this->getAcl($class);
        $oldAces = $acl->getClassAces();

        // @TODO: This is a naive implementation, we should update where
        // possible instead of deleting all old ones and adding all new ones...

        // Delete old ACEs
        foreach (array_reverse(array_keys($oldAces)) as $idx) {
            $acl->deleteClassAce($idx);
        }
        $this->aclProvider->updateAcl($acl);
        // Add new ACEs
        foreach (array_reverse($aces) as $idx => $ace) {
            // If no mask is given, we might as well not insert the ACE
            if ($ace['mask'] === 0) {
                continue;
            }
            $acl->insertClassAce($ace['sid'], $ace['mask']);
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Get ACL object manager.
     *
     * @param object|string $entity or class name
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
     * @param ObjectIdentityInterface[]
     * @return \SplObjectStorage
     */
    public function getACLs(array $oids)
    {
        try {
            return $this->aclProvider->findAcls($oids);
        } catch (NotAllAclsFoundException $e) {
            return $e->getPartialResult();
       } catch (\Symfony\Component\Security\Acl\Exception\Exception $e) {
            return new \SplObjectStorage();
        }
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
     * @param object|string $entity
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
