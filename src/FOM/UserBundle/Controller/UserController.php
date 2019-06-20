<?php
namespace FOM\UserBundle\Controller;

use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use FOM\UserBundle\Component\UserHelperService;
use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Form\Type\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

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
        $allowed_users = array();
        $users = $this->getDoctrine()->getManager()->createQuery('SELECT r FROM FOMUserBundle:User r')->getResult();

        // ACL access check
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

        $form = $this->createForm(new UserType(), $user, array(
            'profile_formtype' => $this->getProfileFormType(),
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->isGranted('OWNER', $oid),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set encrypted password and create new salt
            // The unencrypted password is already set on the user!
            $helperService = $this->getUserHelper();
            $helperService->setPassword($user, $user->getPassword());

            $user->setRegistrationTime(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            try {
                $em->getConnection()->beginTransaction();

                $profile = $user->getProfile();
                $user->setProfile(null);
                $em->persist($user);

                // SQLite needs a flush here
                $em->flush();

                // Check and persists profile if exists
                if ($profile) {
                    $profile->setUid($user);
                    $em->persist($profile);
                }

                $em->flush();

                $em->getConnection()->commit();

                if ($form->has('acl')) {
                    $aces = $form->get('acl')->get('ace')->getData();
                    $this->getAclManager()->setObjectACEs($user, $aces);
                }

                $em->flush();

                // Make sure, the new user has VIEW & EDIT permissions
                $helperService->giveOwnRights($user);

                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }
            $this->addFlash('success', 'The user has been saved.');

            return $this->redirect($this->generateUrl('fom_user_user_index'));
        }
        return $this->render('@FOMUser/User/form.html.twig', array(
            'user'             => $user,
            'form'             => $form->createView(),
            'form_name'        => $form->getName(),
            'edit'             => false,
            'profile_template' => $this->getProfileTemplate(),
            'profile_assets'   => $this->getProfileAssets(),
            'title' => 'fom.user.user.form.new_user',
        ));
    }

    /**
     * @ManagerRoute("/user/{id}/edit", methods={"GET"})
     * @param string $id
     * @return Response
     */
    public function editAction($id)
    {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        /** @var User $user */
        $this->denyAccessUnlessGranted('EDIT', $user);

        $groupPermission =
            $this->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->isGranted('OWNER', $user);

        $form    = $this->createForm(new UserType(), $user, array(
            'requirePassword'  => false,
            'profile_formtype' => $this->getProfileFormType(),
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->isGranted('OWNER', $user),
            'currentUser' => $this->getUser(),
        ));

        return $this->render('@FOMUser/User/form.html.twig', array(
            'user'             => $user,
            'form'             => $form->createView(),
            'form_name'        => $form->getName(),
            'edit'             => true,
            'profile_template' => $this->getProfileTemplate(),
            'profile_assets'   => $this->getProfileAssets(),
            'title' => 'fom.user.user.form.edit_user',
        ));
    }

    /**
     * @ManagerRoute("/user/{id}/update", methods={"POST"})
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function updateAction(Request $request, $id)
    {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        /** @var User $user */

        // ACL access check
        $this->denyAccessUnlessGranted('EDIT', $user);

        // If no password is given, we'll recycle the old one
        $userData     = $request->get('user');
        $keepPassword = false;
        if ($userData['password']['first'] === '' && $userData['password']['second'] === '') {
            $userData['password'] = array(
                'first'  => $user->getPassword(),
                'second' => $user->getPassword());

            $keepPassword = true;
        }
        if (!array_key_exists('username', $userData)) {
            $userData['username'] = $user->getUsername();
        }

        $groupPermission =
            $this->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->isGranted('OWNER', $user);

        $form    = $this->createForm(new UserType(), $user, array(
            'requirePassword'  => false,
            'profile_formtype' => $this->getProfileFormType(),
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->isGranted('OWNER', $user),
            'currentUser' => $this->getUser(),
        ));

        $form->submit($userData);

        if ($form->isValid() && $form->isSubmitted()) {
            if (!$keepPassword) {
                // Set encrypted password and create new salt
                // The unencrypted password is already set on the user!
                /** @var UserHelperService $helperService */
                $helperService = $this->get('fom.user_helper.service');
                $helperService->setPassword($user, $user->getPassword());
            }

            $em = $this->getDoctrine()->getManager();

            // This is the same check as abote in createForm for acl_permission
            if ($this->isGranted('OWNER', $user)) {
                $aces = $form->get('acl')->get('ace')->getData();
                $this->getAclManager()->setObjectACEs($user, $aces);
            }

            $user->getProfile()->setUid($user);
            $em->flush();
            $this->addFlash('success', 'The user has been updated.');

            return $this->redirect($this->generateUrl('fom_user_user_index'));

        }

        return $this->render('@FOMUser/User/form.html.twig', array(
            'user'             => $user,
            'form'             => $form->createView(),
            'form_name'        => $form->getName(),
            'edit'             => true,
            'profile_template' => $this->getProfileTemplate(),
            'profile_assets'   => $this->getProfileAssets(),
            'title' => 'fom.user.user.form.edit_user',
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
        $oid         = ObjectIdentity::fromDomainObject($user);
        $aclProvider->deleteAcl($oid);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $oid         = ObjectIdentity::fromDomainObject($user);
            $aclProvider->deleteAcl($oid);

            $em->remove($user);
            if ($user->getProfile()) {
                $em->remove($user->getProfile());
            }
            $em->flush();
            $em->getConnection()->commit();
            $this->addFlash('success', 'The user has been deleted.');
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $this->addFlash('error', "The user couldn't be deleted.");
        }

        return new Response();
    }

    /**
     * @return string
     */
    protected function getProfileFormType()
    {
        return $this->getParameter('fom_user.profile_formtype');
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

}
