<?php

namespace FOM\UserBundle\Component;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Managed users
 *
 * @alias UserManager
 * @alias User Manager
 *
 */
class FOMIdentitiesProvider implements IdentitiesProviderInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        
        return $this->container->get('doctrine');
    }

    /**
     * @param string $search
     * @return string[]
     *
     */
    public function getUsers($search)
    {
        $qb = $this->getUserRepository()->createQueryBuilder('u');
        

        $query = $qb->where($qb->expr()->like('LOWER(u.username)', ':search'))
            ->setParameter(':search', '%' . strtolower($search) . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery();

        $result = array();
        /** @var  $user UserInterface */
        foreach($query->getResult() as $user) {
            
            $result[] = 'u:' . $user->getUsername();
        }
        return $result;
    }

    public function getRoles() {
        
        
        $groups = $this->getAllGroups();

        $roles = array();
        foreach($groups as $group) {
            $roles[] = 'r:' . $group->getAsRole();
        }

        return $roles;
    }
    
    /**
     * @return array
     */
    public function getAllGroups(){
        return $this->getGroupRepository()->findAll();
        
    }
    
    /**
     * @return UserInterface[]
     */

    public function getAllUsers(){
        return $this->getUserRepository()->findAll();
        
        
    }
    
    /** @return EntityRepository */
    public function getUserRepository(){
        
        return $this->getDoctrine()->getRepository(User::class);
    }
    
    /** @return EntityRepository */
    public function getGroupRepository(){
        
        return $this->getDoctrine()->getRepository(Group::class);
    }
}
