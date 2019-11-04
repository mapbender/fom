<?php

namespace FOM\UserBundle\Controller;

use FOM\UserBundle\Component\AclManager;
use FOM\UserBundle\Component\IdentitiesProviderInterface;
use Mapbender\ManagerBundle\Component\ManagerBundle;
use FOM\ManagerBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class ACLController extends Controller
{
    /**
     * @Route("/acl")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('@FOMUser/ACL/index.html.twig', array(
            'classes' => $this->getACLClasses(),
        ));
    }

    /**
     * @Route("/acl/edit", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request)
    {
        // ACL access check
        $oid = new ObjectIdentity('class', 'Symfony\Component\Security\Acl\Domain\Acl');

        $this->denyAccessUnlessGranted('EDIT', $oid);

        $class = $request->query->get('class');
        $acl_classes = $this->getACLClasses();
        if(!array_key_exists($class, $acl_classes)) {
            throw $this->createNotFoundException('No manageable class given.');
        }

        $form = $this->getClassACLForm($class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AclManager $aclManager */
            $aclManager = $this->get('fom.acl.manager');
            $aclManager->setClassACEs($class, $form->get('ace')->getData());

            return $this->redirectToRoute('fom_user_acl_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Your form has errors, please review them below.');
        }

        return $this->render('@FOMUser/ACL/edit.html.twig', array(
            'class' => $class,
            'class_name' => $acl_classes[$class],
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/acl/overview", methods={"GET"})
     * @return Response
     */
    public function overviewAction()
    {
        /** @var IdentitiesProviderInterface $idProvider */
        $idProvider = $this->get('fom.identities.provider');
        $groups = $idProvider->getAllGroups();
        $users  = $idProvider->getAllUsers();
        return $this->render('@FOMUser/ACL/groups-and-users.html.twig', array(
            'groups' => $groups,
            'users' => $users,
        ));
    }

    /**
     * @param string $class
     * @return \Symfony\Component\Form\Form
     */
    public function getClassACLForm($class)
    {
        return $this->createForm('FOM\UserBundle\Form\Type\ACLType', array(), array(
            'mapped' => false,
            'class' => $class,
            'permissions' => 'standard::class',
            'create_standard_permissions' => false
        ));
    }

    protected function getACLClasses()
    {
        $acl_classes = array();
        foreach($this->get('kernel')->getBundles() as $bundle) {
            if ($bundle instanceof ManagerBundle) {
                $classes = $bundle->getACLClasses();
                if($classes) {
                    $acl_classes = array_merge($acl_classes, $classes);
                }
            }
        }
        return $acl_classes;
    }
}
