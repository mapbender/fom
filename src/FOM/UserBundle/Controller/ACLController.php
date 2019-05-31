<?php

namespace FOM\UserBundle\Controller;

use FOM\UserBundle\Component\AclManager;
use FOM\UserBundle\Component\IdentitiesProviderInterface;
use Mapbender\ManagerBundle\Component\ManagerBundle;
use FOM\ManagerBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Class ACLController
 * @package FOM\UserBundle\Controller
 */
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
     * @Route("/acl/edit", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request)
    {
        // ACL access check
        $oid = new ObjectIdentity('class', 'Symfony\Component\Security\Acl\Domain\Acl');

        $this->denyAccessUnlessGranted('EDIT', $oid);

        $class = $request->get('class');
        $acl_classes = $this->getACLClasses();
        if(!array_key_exists($class, $acl_classes)) {
            throw $this->createNotFoundException('No manageable class given.');
        }

        $form = $this->getClassACLForm($class);

        return $this->render('@FOMUser/ACL/edit.html.twig', array(
            'class' => $class,
            'class_name' => $acl_classes[$class],
            'form' => $form->createView(),
            'form_name' => $form->getName(),
        ));
    }

    /**
     * @Route("/acl/edit", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request)
    {
        // ACL access check
        $oid = new ObjectIdentity('class', 'Symfony\Component\Security\Acl\Domain\Acl');

        $this->denyAccessUnlessGranted('EDIT', $oid);

        $class = $request->get('class');
        $acl_classes = $this->getACLClasses();
        if(!array_key_exists($class, $acl_classes)) {
            throw $this->createNotFoundException('No manageable class given.');
        }

        $form = $this->getClassACLForm($class);
        $form->submit($request);

        if($form->isValid() && $form->isSubmitted()) {
            /** @var AclManager $aclManager */
            $aclManager = $this->get('fom.acl.manager');
            $aclManager->setClassACEs($class, $form->get('ace')->getData());

            return $this->redirect($this->generateUrl('fom_user_acl_index'));
        }

        $this->addFlash('error', 'Your form has errors, please review them below.');

        return $this->render('@FOMUser/ACL/edit.html.twig', array(
            'class' => $class,
            'class_name' => $acl_classes[$class],
            'form' => $form->createView(),
            'form_name' => $form->getName(),
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
        return $this->createForm('acl', array(), array(
            'mapped' => false,
            'class' => $class,
            'permissions' => 'standard::class',
            'create_standard_permissions' => false
        ));
    }

    /**
     * ACL Security Identity typeahead callback
     * If query starts with 'u:', look for user, if it starts with 'r:', look
     * for role, otherwise look for both.
     *
     * @Route("/acl/sid")
     * @param Request $request
     * @return Response
     */
    public function aclsidAction(Request $request)
    {
        $query = $request->get('query');
        $response = array();
        /** @var IdentitiesProviderInterface $idProvider */
        $idProvider = $this->get('fom.identities.provider');

        if($query !== null) {
            switch(substr($query, 0, 2)) {
                case 'u:':
                    $response = $idProvider->getUsers(substr($query, 2));
                    break;
                case 'r:':
                    $response = $idProvider->getRoles();
                    break;
                default:
                    $response = array_merge(
                        $idProvider->getUsers(substr($query, 3)),
                        $idProvider->getRoles());
            }
        }

        return new JsonResponse($response);
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
