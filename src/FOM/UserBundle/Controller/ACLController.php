<?php

namespace FOM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOM\UserBundle\Form\Type\ACLType;

class ACLController extends Controller
{
    /**
     * @Route("/acl")
     * @Template
     */
    public function indexAction()
    {
        return array('classes' => $this->getACLClasses());
    }

    /**
     * @Route("/acl/edit")
     * @Method("GET")
     * @Template
     */
    public function editAction()
    {
        $class = $this->get('request')->get('class');
        $acl_classes = $this->getACLClasses();
        if(!array_key_exists($class, $acl_classes)) {
            throw $this->createNotFoundException('No manageable class given.');
        }

        $form = $this->getClassACLForm($class);

        return array(
            'class' => $class,
            'class_name' => $acl_classes[$class],
            'form' => $form->createView(),
            'form_name' => $form->getName());
    }

    /**
     * @Route("/acl/edit")
     * @Method("POST")
     * @Template
     */
    public function updateAction()
    {
        $class = $this->get('request')->get('class');
        $acl_classes = $this->getACLClasses();
        if(!array_key_exists($class, $acl_classes)) {
            throw $this->createNotFoundException('No manageable class given.');
        }

        $form = $this->getClassACLForm($class);
        $request = $this->getRequest();
        $form->bindRequest($request);
        if($form->isValid()) {
            $aclManager = $this->get('fom.acl.manager');
            $aclManager->setClassACLFromForm($class, $form, 'object');

            return $this->redirect($this->generateUrl('fom_user_acl_index'));
        }

        $this->get('session')->setFlash('error',
            'Your form has errors, please review them below.');

        return array(
            'class' => $class,
            'class_name' => $acl_classes[$class],
            'form' => $form,
            'form_name' => $form->getName());
    }

    public function getClassACLForm($class)
    {
        return $this->createForm(new ACLType(
            $this->get('security.context'),
            $this->get('security.acl.provider'),
            $this->get('router')), array(), array(
                'property_path' => false,
                'class' => $class,
                'permissions' => 'standard::class',
                'create_standard_permissions' => false,
                'label_render' => false
            ));
    }

    /**
     * ACL Security Identity typeahead callback
     * If query starts with 'u:', look for user, if it starts with 'r:', look
     * for role, otherwise look for both.
     *
     * @Route("/acl/sid")
     */
    public function aclsidAction()
    {
        $query = $this->get('request')->get('query');
        $response = array();

        if($query !== null) {
            switch(substr($query, 0, 2)) {
                case 'u:':
                    $response = $this->getUsers(substr($query, 3));
                    break;
                case 'r:':
                    $response = $this->getRoles(substr($query, 3));
                    break;
                default:
                    $response = array_merge(
                        $this->getUsers(substr($query, 3)),
                        $this->getRoles(substr($query, 3)));
            }
        }

        return new Response(json_encode($response), 200, array(
            'Content-Type' => 'application/json'));
    }

    /**
     * Get user security identifiers for given query.
     *
     * @param  string $search Query string
     * @return array         Array of user security identifiers
     */
    protected function getUsers($search)
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

    /**
     * Get role security identifiers for given query.
     *
     * @param  string $query Query string
     * @return array         Array of role security identifiers
     */
    protected function getRoles($query) {
        return array(
            'r:zyx',
            'r:123'
        );
    }

    protected function getACLClasses()
    {
        $acl_classes = array();
        foreach($this->get('kernel')->getBundles() as $bundle) {
            if(is_subclass_of($bundle, 'FOM\ManagerBundle\Component\ManagerBundle')) {
                $classes = $bundle->getACLClasses();
                if($classes) {
                    $acl_classes = array_merge($acl_classes, $classes);
                }
            }
        }
        return $acl_classes;
    }
}
