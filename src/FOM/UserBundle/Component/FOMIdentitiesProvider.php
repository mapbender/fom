<?php

namespace FOM\UserBundle\Component;

use Symfony\Component\DependencyInjection\ContainerAware;


class FOMIdentitiesProvider extends ContainerAware implements IdentitiesProviderInterface
{
    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    public function getUsers($search)
    {
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        $qb = $repo->createQueryBuilder('u');

        $query = $qb->where($qb->expr()->like('LOWER(u.username)', ':search'))
            ->setParameter(':search', '%' . strtolower($search) . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery();

        $result = array();
        foreach($query->getResult() as $user) {
            $result[] = 'u:' . $user->getUsername();
        }
        return $result;
    }

    public function getRoles() {
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:Group');
        $groups = $repo->findAll();

        $roles = array();
        foreach($groups as $group) {
            $roles[] = 'r:' . $group->getAsRole();
        }

        return $roles;
    }

    public function getAllGroups(){
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:Group');
        $groups = $repo->findAll();

        $all = array();
        foreach($groups as $group) {
            $all[] = $group;
        }

        return $all;
    }

    public function getAllUsers(){
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        $users = $repo->findAll();

        $all = array();
        foreach($users as $user) {
            $all[] = $user;
        }

        return $all;
    }
}
