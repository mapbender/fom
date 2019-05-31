<?php

namespace FOM\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOM\UserBundle\Component\RolesService;
use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Form\Type\GroupType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOM\ManagerBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Group management controller
 *
 * @author Christian Wygoda
 */
class GroupController extends UserControllerBase
{
    /**
     * Renders group list.
     *
     * @Route("/group", methods={"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group');

        $query = $this->getEntityManager()->createQuery('SELECT g FROM FOMUserBundle:Group g');
        $groups = $query->getResult();
        $allowed_groups = array();
        // ACL access check
        foreach($groups as $index => $group) {
            if ($this->isGranted('VIEW', $group)) {
                $allowed_groups[] = $group;
            }
        }

        return $this->render('@FOMUser/Group/index.html.twig', array(
            'groups' => $allowed_groups,
            'create_permission' => $this->isGranted('CREATE', $oid),
        ));
    }

    /**
     * @Route("/group/new", methods={"GET"})
     * @return Response
     */
    public function newAction() {
        $group = new Group();

        // ACL access check
        $oid = new ObjectIdentity('class', get_class($group));

        $this->denyAccessUnlessGranted('CREATE', $oid);

        $form = $this->createForm(new GroupType(), $group);

        return $this->render('@FOMUser/Group/form.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
            'edit' => false,
        ));
    }

    /**
     * @Route("/group", methods={"POST"})
     *
     * There is one weirdness when storing groups: In Doctrine Many-to-Many
     * associations, updates are only written, when the owning side changes.
     * For the User-Group association, the user is the owner part.
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $group = new Group();

        // ACL access check
        $oid = new ObjectIdentity('class', get_class($group));

        $this->denyAccessUnlessGranted('CREATE', $oid);

        $available_roles = $this->getRolesService()->getAll();
        $form = $this
            ->createForm(new GroupType(), $group, array('available_roles' => $available_roles))
            ->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);

            // See method documentation for Doctrine weirdness
            foreach($group->getUsers() as $user) {
                /** @var User $user */
                $user->addGroups($group);
            }

            $em->flush();

            // creating the ACL
            $aclProvider = $this->getAclProvider();
            $objectIdentity = ObjectIdentity::fromDomainObject($group);
            $acl = $aclProvider->createAcl($objectIdentity);

            // retrieving the security identity of the currently logged-in user
            $securityIdentity = UserSecurityIdentity::fromAccount($this->getUser());

            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            $this->addFlash('success', 'The group has been saved.');

            return $this->redirect(
                $this->generateUrl('fom_user_group_index')
            );
        }

        return $this->render('@FOMUser/Group/form.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
            'edit' => false,
        ));
    }

    /**
     * @Route("/group/{id}/edit", methods={"GET"})
     * @param string $id
     * @return Response
     */
    public function editAction($id)
    {
        $group = $this->getDoctrine()->getRepository('FOMUserBundle:Group')
            ->find($id);

        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }

        // ACL access check
        $this->denyAccessUnlessGranted('EDIT', $group);

        $form = $this->createForm(new GroupType(), $group);

        return $this->render('@FOMUser/Group/form.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
            'edit' => true,
        ));
    }

    /**
     * @Route("/group/{id}/update", methods={"POST"})
     *
     * There is one weirdness when storing groups: In Doctrine Many-to-Many
     * associations, updates are only written, when the owning side changes.
     * For the User-Group association, the user is the owner part.
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getEntityManager();
        $group = $em->getRepository('FOMUserBundle:Group')->find($id);
        if($group === null) {
            throw new NotFoundHttpException('The group does not exist');
        }
        /** @var Group $group */

        $this->denyAccessUnlessGranted('EDIT', $group);

        // See method documentation for Doctrine weirdness
        $old_users = clone $group->getUsers();

        $form = $this->createForm(new GroupType(), $group, array(
            'available_roles' => $this->getRolesService()->getAll(),
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {

            // See method documentation for Doctrine weirdness
            foreach($old_users as $user) {
                /** @var User $user */
                $user->getGroups()->removeElement($group);
            }

            foreach($group->getUsers() as $user) {
                $user->addGroups($group);
            }

            $em->flush();

            $this->addFlash('success', 'The group has been updated.');

            return $this->redirect(
                $this->generateUrl('fom_user_group_index')
            );
        }

        return $this->render('@FOMUser/Group/form.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
            'edit' => true,
        ));
    }

    /**
     * @Route("/group/{id}/delete", methods={"POST"})
     * @param string $id
     * @return Response
     */
    public function deleteAction($id)
    {
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

            $oid = ObjectIdentity::fromDomainObject($group);
            $this->getAclProvider()->deleteAcl($oid);

            $em->flush();

        } catch(\Exception $e) {
            $this->addFlash('error', "The group couldn't be deleted.");
        }
        return new Response();
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        return $em;
    }

    /**
     * @return RolesService
     */
    protected function getRolesService()
    {
        /** @var RolesService $service */
        $service = $this->get('fom_roles');
        return $service;
    }
}
