<?php

namespace FOM\UserBundle\Controller;

use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Form\Type\GroupType;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $groups = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->findAll();

        return array(
            'groups' => $groups);
    }

    /**
     * @Route("/group/new")
     * @Method({ "GET" })
     * @Template
     */
    public function newAction() {
        $available_roles = $this->get('fom_roles')->getAll();
        $group = new Group();
        $form = $this->createForm(new GroupType(), $group, array(
            'available_roles' => $available_roles));

        return array(
            'group' => $group,
            'form' => $form->createView(),
            'form_name' => $form->getName());
    }

    /**
     * @Route("/group")
     * @Method({ "POST" })
     * @Template("MapbenderManagerBundle:Group:new.html.twig")
     *
     * There is one weirdness when storing groups: In Doctrine Many-to-Many
     * associations, updates are only written, when the owning side changes.
     * For the User-Group association, the user is the owner part.
     */
    public function createAction() {
        $available_roles = $this->get('fom_roles')->getAll();
        $group = new Group();
        $form = $this->createForm(new GroupType(), $group, array(
            'available_roles' => $available_roles));

        $form->bindRequest($this->get('request'));

        if($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($group);

            // See method documentation for Doctrine weirdness
            foreach($group->getUsers() as $user) {
                $user->addGroups($group);
            }

            $em->flush();

            $this->get('session')->setFlash('success',
                'The group has been saved.');

            return $this->redirect(
                $this->generateUrl('fom_user_group_index'));
        }

        return array(
            'group' => $group,
            'form' => $form->createView());
    }

    /**
     * @Route("/group/{id}/edit")
     * @Method({ "GET" })
     * @Template
     */
    public function editAction($id) {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);
        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        $available_roles = $this->get('fom_roles')->getAll();
        $form = $this->createForm(new GroupType(), $group, array(
            'available_roles' => $available_roles));

        return array(
            'group' => $group,
            'form' => $form->createView(),
            'form_name' => $form->getName());
    }

    /**
     * @Route("/group/{id}/update")
     * @Method({ "POST" })
     * @Template("MapbenderManagerBundle:Group:edit.html.twig")
     *
     * There is one weirdness when storing groups: In Doctrine Many-to-Many
     * associations, updates are only written, when the owning side changes.
     * For the User-Group association, the user is the owner part.
     */
    public function updateAction($id) {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);
        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        // See method documentation for Doctrine weirdness
        $old_users = clone $group->getUsers();

        $available_roles = $this->get('fom_roles')->getAll();
        $form = $this->createForm(new GroupType(), $group, array(
            'available_roles' => $available_roles));
        $form->bindRequest($this->get('request'));

        if($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();

            // See method documentation for Doctrine weirdness
            foreach($old_users as $user) {
                $user->getGroups()->removeElement($group);
            }
            foreach($group->getUsers() as $user) {
                $user->addGroups($group);
            }

            $em->flush();

            $this->get('session')->setFlash('success',
                'The group has been updated.');

            return $this->redirect(
                $this->generateUrl('fom_user_group_index'));

        }

        return array(
            'group' => $group,
            'form' => $form->createView());
    }

    /**
     * @Route("/group/{id}/delete")
     * @Method({ "GET" })
     * @Template("FOMUserBundle:Group:delete.html.twig")
     */
    public function confirmDeleteAction($id) {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);
        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        $form = $this->createDeleteForm($id);

        return array(
            'group' => $group,
            'form' => $form->createView());
    }

    /**
     * @Route("/group/{id}/delete")
     * @Method({ "POST" })
     * @Template
     */
    public function deleteAction($id) {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);

        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);
        if($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($group);
            $em->flush();

            $this->get('session')->setFlash('success',
                'The group has been deleted.');
        } else {
            $this->get('session')->setFlash('error',
                'The group couldn\'t be deleted.');
        }
        return $this->redirect(
            $this->generateUrl('fom_user_group_index'));
    }

    /**
     * Creates the form for the confirm delete page.
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}

