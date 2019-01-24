<?php

namespace FOM\UserBundle\Controller;

use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Form\Type\GroupType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Group management controller
 *
 * @author Christian Wygoda
 */
class GroupController extends Controller {
    /**
     * Renders group list.
     *
     * @Route("/group")
     * @Method({ "GET" })
     * @Template
     */
    public function indexAction() {
        $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group');

        $query = $this->getDoctrine()->getManager()->createQuery('SELECT g FROM FOMUserBundle:Group g');
        $groups = $query->getResult();
        $allowed_groups = array();
        // ACL access check
        foreach($groups as $index => $group) {
            if($this->get('security.authorization_checker')->isGranted('VIEW', $group)) {
                $allowed_groups[] = $group;
            }
        }

        return array(
            'groups' => $allowed_groups,
            'create_permission' => $this->get('security.authorization_checker')->isGranted('CREATE', $oid)
        );
    }

    /**
     * @Route("/group/new")
     * @Method({ "GET" })
     * @Template("FOMUserBundle:Group:form.html.twig")
     */
    public function newAction() {
        $group = new Group();

        // ACL access check
        $oid = new ObjectIdentity('class', get_class($group));

        $this->denyAccessUnlessGranted('CREATE', $oid);

        $form = $this->createForm(new GroupType(), $group);

        return array(
            'group' => $group,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => false
        );
    }

    /**
     * @Route("/group")
     * @Method({ "POST" })
     * @Template("FOMUserBundle:Group:form.html.twig")
     *
     * There is one weirdness when storing groups: In Doctrine Many-to-Many
     * associations, updates are only written, when the owning side changes.
     * For the User-Group association, the user is the owner part.
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function createAction(Request $request) {
        $group = new Group();

        // ACL access check
        $oid = new ObjectIdentity('class', get_class($group));

        $this->denyAccessUnlessGranted('CREATE', $oid);

        $available_roles = $this->get('fom_roles')->getAll();
        $form = $this
            ->createForm(new GroupType(), $group, array('available_roles' => $available_roles))
            ->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);

            // See method documentation for Doctrine weirdness
            foreach($group->getUsers() as $user) {
                $user->addGroups($group);
            }

            $em->flush();

            // creating the ACL
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($group);
            $acl = $aclProvider->createAcl($objectIdentity);

            // retrieving the security identity of the currently logged-in user
            $securityIdentity = UserSecurityIdentity::fromAccount($this->getUser());

            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            $this->get('session')->getFlashBag()->set('success',
                'The group has been saved.');

            return $this->redirect(
                $this->generateUrl('fom_user_group_index')
            );
        }

        return array(
            'group' => $group,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => false);
    }

    /**
     * @Route("/group/{id}/edit")
     * @Method({ "GET" })
     * @Template("FOMUserBundle:Group:form.html.twig")
     */
    public function editAction($id) {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);

        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        // ACL access check
        $this->denyAccessUnlessGranted('EDIT', $group);

        $form = $this->createForm(new GroupType(), $group);

        return array(
            'group' => $group,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => true
        );
    }

    /**
     * @Route("/group/{id}/update")
     * @Method({ "POST" })
     * @Template("FOMUserBundle:Group:form.html.twig")
     *
     * There is one weirdness when storing groups: In Doctrine Many-to-Many
     * associations, updates are only written, when the owning side changes.
     * For the User-Group association, the user is the owner part.
     * @param $id
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction($id, Request $request) {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);
        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        // ACL access check
        $this->denyAccessUnlessGranted('EDIT', $group);

        // See method documentation for Doctrine weirdness
        $old_users = clone $group->getUsers();

        $form = $this
            ->createForm(new GroupType(), $group, array('available_roles' => $this->get('fom_roles')->getAll()))
            ->handleRequest($request);

        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // See method documentation for Doctrine weirdness
            foreach($old_users as $user) {
                $user->getGroups()->removeElement($group);
            }

            foreach($group->getUsers() as $user) {
                $user->addGroups($group);
            }

            $em->flush();

            $this->get('session')->getFlashBag()->set('success', 'The group has been updated.');

            return $this->redirect(
                $this->generateUrl('fom_user_group_index')
            );
        }

        return array(
            'group' => $group,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => true
        );
    }

    /**
     * @Route("/group/{id}/delete")
     * @Method({ "POST" })
     */
    public function deleteAction($id) {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);

        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        try {
            // ACL access check
            $this->denyAccessUnlessGranted('DELETE', $group);

            $em = $this->getDoctrine()->getManager();
            $em->remove($group);

            $aclProvider = $this->get('security.acl.provider');
            $oid = ObjectIdentity::fromDomainObject($group);
            $aclProvider->deleteAcl($oid);

            $em->flush();

        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->set('error',
                'The group couldn\'t be deleted.');
        }
        return new Response();
    }
}
