<?php
namespace FOM\UserBundle\Controller;

use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Form\Type\UserType;
use FOM\UserBundle\Security\UserHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * User management controller
 *
 * @author Christian Wygoda
 */
class UserController extends Controller
{
    /**
     * Renders user list.
     *
     * @ManagerRoute("/user")
     * @Method({ "GET" })
     * @Template
     */
    public function indexAction()
    {
        $allowed_users = array();
        $users = $this->getDoctrine()->getManager()->createQuery('SELECT r FROM FOMUserBundle:User r')->getResult();

        // ACL access check
        foreach ($users as $index => $user) {
            if ($this->get('security.authorization_checker')->isGranted('VIEW', $user)) {
                $allowed_users[] = $user;
            }
        }

        $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\User');

        return array(
            'users'             => $allowed_users,
            'create_permission' => $this->get('security.authorization_checker')->isGranted('CREATE', $oid)
        );
    }

    /**
     * @ManagerRoute("/user/new")
     * @Method({ "GET" })
     * @Template("FOMUserBundle:User:form.html.twig")
     */
    public function newAction()
    {
        $user = new User();

        // ACL access check
        $oid = new ObjectIdentity('class', get_class($user));

        $this->denyAccessUnlessGranted('CREATE', $oid);

        $groupPermission =
            $this
                ->get('security.authorization_checker')
                ->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->get('security.authorization_checker')->isGranted('OWNER', $oid);

        $profile = $this->addProfileForm($user);
        $form    = $this->createForm(new UserType(), $user, array(
            'profile_formtype' => $profile['formtype'],
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->get('security.authorization_checker')->isGranted('OWNER', $oid),
        ));

        return array(
            'user'             => $user,
            'form'             => $form->createView(),
            'form_name'        => $form->getName(),
            'edit'             => false,
            'profile_template' => $profile['template'],
            'profile_assets'   => $profile['assets']
        );
    }

    /**
     * @ManagerRoute("/user")
     * @Method({ "POST" })
     * @Template("FOMUserBundle:User:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $user = new User();

        // ACL access check
        $oid = new ObjectIdentity('class', get_class($user));
        $this->denyAccessUnlessGranted('CREATE', $oid);

        $groupPermission =
            $this->get('security.authorization_checker')->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->get('security.authorization_checker')->isGranted('OWNER', $oid);

        $profile = $this->addProfileForm($user);

        $form = $this->createForm(new UserType(), $user, array(
            'profile_formtype' => $profile['formtype'],
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->get('security.authorization_checker')->isGranted('OWNER', $oid),
        ));

        $form->submit($request);

        if ($form->isValid() && $form->isSubmitted()) {
            // Set encrypted password and create new salt
            // The unencrypted password is already set on the user!
            $helper = new UserHelper($this->container);
            $helper->setPassword($user, $user->getPassword());

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
                    $aclManager = $this->get('fom.acl.manager');
                    $aclManager->setObjectACLFromForm($user, $form->get('acl'), 'object');
                }

                $em->flush();

                // Make sure, the new user has VIEW & EDIT permissions
                $helper->giveOwnRights($user);

                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }

            $this->get('session')->getFlashBag()->set('success', 'The user has been saved.');

            return $this->redirect($this->generateUrl('fom_user_user_index'));
        }

        $this->get('session')->getFlashBag()->set('error', 'There are field validation errors.');

        return array(
            'user'             => $user,
            'form'             => $form->createView(),
            'form_name'        => $form->getName(),
            'edit'             => false,
            'profile_template' => $profile['template'],
            'profile_assets'   => $profile['assets']);
    }

    /**
     * @ManagerRoute("/user/{id}/edit")
     * @Method({ "GET" })
     * @Template("FOMUserBundle:User:form.html.twig")
     */
    public function editAction($id)
    {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }

        // ACL access check
        $this->denyAccessUnlessGranted('EDIT', $user);

        $groupPermission =
            $this->get('security.authorization_checker')->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->get('security.authorization_checker')->isGranted('OWNER', $user);

        $profile = $this->addProfileForm($user);
        $form    = $this->createForm(new UserType(), $user, array(
            'requirePassword'  => false,
            'profile_formtype' => $profile['formtype'],
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->get('security.authorization_checker')->isGranted('OWNER', $user),
            'currentUser' => $this->get('security.authorization_checker')->getToken()->getUser()
        ));

        return array(
            'user'             => $user,
            'form'             => $form->createView(),
            'form_name'        => $form->getName(),
            'edit'             => true,
            'profile_template' => $profile['template'],
            'profile_assets'   => $profile['assets']);
    }

    /**
     * @ManagerRoute("/user/{id}/update")
     * @Method({ "POST" })
     * @Template("FOMUserBundle:User:form.html.twig")
     */
    public function updateAction($id)
    {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }

        // ACL access check
        $this->denyAccessUnlessGranted('EDIT', $user);

        // If no password is given, we'll recycle the old one
        $request      = $this->get('request_stack')->getCurrentRequest();
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
            $this->get('security.authorization_checker')->isGranted('EDIT', new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group'))
            || $this->get('security.authorization_checker')->isGranted('OWNER', $user);

        $profile = $this->addProfileForm($user);
        $form    = $this->createForm(new UserType(), $user, array(
            'requirePassword'  => false,
            'profile_formtype' => $profile['formtype'],
            'group_permission' => $groupPermission,
            'acl_permission'   => $this->get('security.authorization_checker')->isGranted('OWNER', $user),
            'currentUser' => $this->get('security.authorization_checker')->getToken()->getUser()
        ));

        $form->submit($userData);

        if ($form->isValid() && $form->isSubmitted()) {
            if (!$keepPassword) {
                // Set encrypted password and create new salt
                // The unencrypted password is already set on the user!
                $helper = new UserHelper($this->container);
                $helper->setPassword($user, $user->getPassword());
            }

            $em = $this->getDoctrine()->getManager();

            // This is the same check as abote in createForm for acl_permission
            if ($this->get('security.authorization_checker')->isGranted('OWNER', $user)) {
                $aclManager = $this->get('fom.acl.manager');
                $aclManager->setObjectACLFromForm($user, $form->get('acl'), 'object');
            }

            $user->getProfile()->setUid($user);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success', 'The user has been updated.');

            return $this->redirect($this->generateUrl('fom_user_user_index'));

        }

        return array(
            'user'             => $user,
            'form'             => $form->createView(),
            'form_name'        => $form->getName(),
            'edit'             => true,
            'profile_template' => $profile['template'],
            'profile_assets'   => $profile['assets']
        );
    }

    /**
     * @ManagerRoute("/user/{id}/delete")
     * @Method({ "POST" })
     *
     * @todo : Delete ACEs for given user
     */
    public function deleteAction($id)
    {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);

        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        if ($user->getId() === 1) {
            throw new NotFoundHttpException('The root user can not be deleted');
        }

        // ACL access check
        $this->denyAccessUnlessGranted('DELETE', $user);

        $aclProvider = $this->get('security.acl.provider');
        $oid         = ObjectIdentity::fromDomainObject($user);
        $aclProvider->deleteAcl($oid);

        $this->addProfileForm($user);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $aclProvider = $this->get('security.acl.provider');
            $oid         = ObjectIdentity::fromDomainObject($user);
            $aclProvider->deleteAcl($oid);

            $em->remove($user);
            if ($user->getProfile()) {
                $em->remove($user->getProfile());
            }
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->set('success', 'The user has been deleted.');
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->set('error', "The user couldn't be deleted.");
        }

        return new Response();
    }

    /**
     * @param User $user
     * @return array
     */
    private function addProfileForm(User $user)
    {
        $container       = $this->container;
        $profileFormType = $container->getParameter('fom_user.profile_formtype');
        $profileTemplate = $container->getParameter('fom_user.profile_template');
        $profileAssets   = $container->getParameter('fom_user.profile_assets');

        return array(
            'formtype' => $profileFormType,
            'template' => $profileTemplate,
            'assets'   => $profileAssets
        );
    }
}
