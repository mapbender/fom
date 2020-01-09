<?php
namespace FOM\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use FOM\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * User management controller
 *
 * @author Christian Wygoda
 */
class UserController extends UserControllerBase
{
    /**
     * Renders user list.
     *
     * @ManagerRoute("/user", methods={"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $users = $this->getEntityManager()->getRepository('FOMUserBundle:User')->findAll();
        $allowed_users = array();

        // Bulk-prefetch ACLs for all User entities into AclProvider's internal cache
        $oids = array();
        foreach ($users as $index => $user) {
            $oids[] = ObjectIdentity::fromDomainObject($user);
        }
        $this->getAclManager()->getACLs($oids);

        foreach ($users as $index => $user) {
            if ($this->isGranted('VIEW', $user)) {
                $allowed_users[] = $user;
            }
        }

        $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\User');

        return $this->render('@FOMUser/User/index.html.twig', array(
            'users'             => $allowed_users,
            'create_permission' => $this->isGranted('CREATE', $oid),
            'title' => $this->translate('fom.user.user.index.title'),
        ));
    }

    /**
     * @ManagerRoute("/user/new", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $user = new User();

        // ACL access check
        $oid = new ObjectIdentity('class', get_class($user));
        $this->denyAccessUnlessGranted('CREATE', $oid);

        $groupPermission =
            $this->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->isGranted('OWNER', $oid);

        $form = $this->createForm('FOM\UserBundle\Form\Type\UserType', $user, array(
            'requirePassword' => true,
            'profile_formtype' => $this->getProfileFormType(),
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->isGranted('OWNER', $user),
            'currentUser' => $this->getUser(),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRegistrationTime(new \DateTime());

            $em = $this->getEntityManager();
            $em->beginTransaction();

            try {
                $this->persistUser($em, $user);

                if ($form->has('acl')) {
                    $aces = $form->get('acl')->get('ace')->getData();
                    $this->getAclManager()->setObjectACEs($user, $aces);
                }

                $em->flush();

                // Make sure, the new user has VIEW & EDIT permissions
                $this->getUserHelper()->giveOwnRights($user);

                $em->commit();
            } catch (\Exception $e) {
                $em->rollback();
                throw $e;
            }
            $this->addFlash('success', 'The user has been saved.');

            return $this->redirectToRoute('fom_user_user_index');
        }
        return $this->render('@FOMUser/User/form.html.twig', array(
            'user'             => $user,
            'form'             => $form->createView(),
            'edit'             => false,
            'profile_template' => $this->getProfileTemplate(),
            'profile_assets'   => $this->getProfileAssets(),
            'title' => $this->translate('fom.user.user.form.new_user'),
        ));
    }

    /**
     * @ManagerRoute("/user/{id}/edit", methods={"GET", "POST"})
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        /** @var User|null $user */
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }

        $this->denyAccessUnlessGranted('EDIT', $user);

        $groupPermission =
            $this->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->isGranted('OWNER', $user);

        $form    = $this->createForm('FOM\UserBundle\Form\Type\UserType', $user, array(
            'requirePassword'  => false,
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->isGranted('OWNER', $user),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEntityManager();
            $this->persistUser($em, $user);

            if ($form->has('acl')) {
                $aces = $form->get('acl')->get('ace')->getData();
                $this->getAclManager()->setObjectACEs($user, $aces);
            }

            $em->flush();
            $this->addFlash('success', 'The user has been updated.');

            return $this->redirectToRoute('fom_user_user_index');

        }

        return $this->render('@FOMUser/User/form.html.twig', array(
            'user'             => $user,
            'form'             => $form->createView(),
            'edit'             => true,
            'profile_template' => $this->getProfileTemplate(),
            'profile_assets'   => $this->getProfileAssets(),
            'title' => $this->translate('fom.user.user.form.edit_user'),
        ));
    }

    /**
     * @ManagerRoute("/user/{id}/delete", methods={"POST"})
     * @param string $id
     * @return Response
     *
     * @todo : Delete ACEs for given user
     */
    public function deleteAction($id)
    {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);

        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        /** @var User $user */
        if ($user->getId() === 1) {
            throw new NotFoundHttpException('The root user can not be deleted');
        }

        $this->denyAccessUnlessGranted('DELETE', $user);
        $aclProvider = $this->getAclProvider();

        $em = $this->getEntityManager();
        $em->beginTransaction();

        try {
            if ($aclProvider instanceof MutableAclProvider) {
                $sid = UserSecurityIdentity::fromAccount($user);
                $aclProvider->deleteSecurityIdentity($sid);
            }
            $oid = ObjectIdentity::fromDomainObject($user);
            $aclProvider->deleteAcl($oid);

            $em->remove($user);
            if ($user->getProfile()) {
                $em->remove($user->getProfile());
            }
            $em->flush();
            $em->commit();
            $this->addFlash('success', 'The user has been deleted.');
        } catch (\Exception $e) {
            $em->rollback();
            $this->addFlash('error', "The user couldn't be deleted.");
        }

        return new Response();
    }

    /**
     * @return string
     */
    protected function getProfileTemplate()
    {
        return $this->getParameter('fom_user.profile_template');
    }

    /**
     * @return mixed
     */
    protected function getProfileAssets()
    {
        return $this->getParameter('fom_user.profile_assets');
    }

    /**
     * @param EntityManagerInterface $em
     * @param User $user
     * @internal
     */
    protected function persistUser(EntityManagerInterface $em, User $user)
    {
        if (($profile = $user->getProfile()) && !$user->getId()) {
            // flush user without profile to generate user pk first, then restore profile
            // @todo: invert bad relation direction user => profile (currently the profile owns the user)
            $user->setProfile(null);
            $em->persist($user);
            $em->flush();
            $user->setProfile($profile);
            $em->persist($profile);
        }
        $em->persist($user);
    }
}
