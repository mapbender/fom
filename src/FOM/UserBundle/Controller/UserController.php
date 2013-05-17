<?php

namespace FOM\UserBundle\Controller;


use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Form\Type\UserType;
use FOM\UserBundle\Security\UserHelper;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;


/**
 * User management controller
 *
 * @author Christian Wygoda
 */
class UserController extends Controller {
    /**
     * Renders user list.
     *
     * @ManagerRoute("/user")
     * @Method({ "GET" })
     * @Template
     */
    public function indexAction() {
        $securityContext = $this->get('security.context');
        $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\User');

        $query = $this->getDoctrine()->getEntityManager()->createQuery('SELECT r FROM FOMUserBundle:User r');
        $users = $query->getResult();
        $allowed_users = array();
        // ACL access check
        foreach($users as $index => $user) {
            if($securityContext->isGranted('VIEW', $user)) {
                $allowed_users[] = $user;
            } else {

            }
        }

        return array(
            'users' => $allowed_users,
            'create_permission' => $securityContext->isGranted('CREATE', $oid));
    }

    /**
     * @ManagerRoute("/user/new")
     * @Method({ "GET" })
     * @Template("FOMUserBundle:User:form.html.twig")
     */
    public function newAction() {
        $user = new User();

        // ACL access check
        $securityContext = $this->get('security.context');
        $oid = new ObjectIdentity('class', get_class($user));
        if(false === $securityContext->isGranted('CREATE', $oid)) {
            throw new AccessDeniedException();
        }

        $profile = $this->addProfileForm($user);
        $form = $this->createForm(new UserType(), $user, array(
            'profile_formtype' => $profile['formtype']
        ));

        return array(
            'user' => $user,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => false,
            'profile_template' => $profile['template']);
    }

    /**
     * @ManagerRoute("/user")
     * @Method({ "POST" })
     * @Template("FOMUserBundle:User:form.html.twig")
     */
    public function createAction() {
        $user = new User();
        $profile = $this->addProfileForm($user);
        $form = $this->createForm(new UserType(), $user, array(
            'profile_formtype' => $profile['formtype']
        ));



        // ACL access check
        $securityContext = $this->get('security.context');
        $oid = new ObjectIdentity('class', get_class($user));
        if(false === $securityContext->isGranted('CREATE', $oid)) {
            throw new AccessDeniedException();
        }



        $form->bindRequest($this->get('request'));

        if($form->isValid()) {
            // Set encrypted password and create new salt
            // The unencrypted password is already set on the user!
            $helper = new UserHelper($this->container);
            $helper->setPassword($user, $user->getPassword());

            $user->setRegistrationTime(new \DateTime());

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $em->flush();

            $aclManager = $this->get('fom.acl.manager');
            $aclManager->setObjectACLFromForm($user, $form->get('acl'),
                'object');

            // Check and persists profile if exists
            $profile = $user->getProfile();
            if($profile) {
                $em->persist($profile);
            }

            $em->flush();

            $this->get('session')->setFlash('success',
                'The user has been saved.');

            return $this->redirect(
                $this->generateUrl('fom_user_user_index'));
        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => false,
            'profile_template' => $profile['template']);
    }

    /**
     * @ManagerRoute("/user/{id}/edit")
     * @Method({ "GET" })
     * @Template("FOMUserBundle:User:form.html.twig")
     */
    public function editAction($id) {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);
        if($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }

        // ACL access check
        $securityContext = $this->get('security.context');
        if(false === $securityContext->isGranted('EDIT', $user)) {
            throw new AccessDeniedException();
        }

        $profile = $this->addProfileForm($user);
        $form = $this->createForm(new UserType(), $user, array(
            'requirePassword' => false,
            'profile_formtype' => $profile['formtype']
        ));

        return array(
            'user' => $user,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => true,
            'profile_template' => $profile['template']);
    }

    /**
     * @ManagerRoute("/user/{id}/update")
     * @Method({ "POST" })
     * @Template("FOMUserBundle:User:form.html.twig")
     */
    public function updateAction($id) {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')->find($id);
        if($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }

        // ACL access check
        $securityContext = $this->get('security.context');
        if(false === $securityContext->isGranted('EDIT', $user)) {
            throw new AccessDeniedException();
        }
        // If no password is given, we'll recycle the old one
        $request = $this->get('request');
        $userData = $request->get('user');
        $keepPassword = false;
        if($userData['password']['first'] === '' && $userData['password']['second'] === '') {
            $userData['password'] = array(
                'first' => $user->getPassword(),
                'second' => $user->getPassword());

            $keepPassword = true;
        }

        $profile = $this->addProfileForm($user);
        $form = $this->createForm(new UserType(), $user, array(
            'requirePassword' => false,
            'profile_formtype' => $profile['formtype']
        ));
        $form->bind($userData);

        if($form->isValid()) {
            if(!$keepPassword) {
                // Set encrypted password and create new salt
                // The unencrypted password is already set on the user!
                $helper = new UserHelper($this->container);
                $helper->setPassword($user, $user->getPassword());
            }

            $em = $this->getDoctrine()->getEntityManager();

            $aclManager = $this->get('fom.acl.manager');
            $aclManager->setObjectACLFromForm($user, $form->get('acl'),
                'object');

            if($user->getProfile()) {
                $em->persist($user->getProfile());
            }
            $em->flush();

            $this->get('session')->setFlash('success', 'The user has been updated.');

            return $this->redirect($this->generateUrl('fom_user_user_index'));

        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
            'edit' => true,
            'profile_template' => $profile['template']);
    }

    /**
     * @ManagerRoute("/user/{id}/delete")
     * @Method({ "POST" })
     *
     * @todo : Delete ACEs for given user
     */
    public function deleteAction($id) {
        $user = $this->getDoctrine()->getRepository('FOMUserBundle:User')
            ->find($id);

        if($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        if($user->getId() === 1) {
            throw new NotFoundHttpException('The root user can not be deleted');
        }

        // ACL access check
        $securityContext = $this->get('security.context');
        if(false === $securityContext->isGranted('DELETE', $user)) {
            throw new AccessDeniedException();
        }

        $this->addProfileForm($user);
        $profile = $user->getProfile();
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        try {
            $em = $this->getDoctrine()->getEntityManager();

            $em->remove($user);
            if($user->getProfile()) {
                $em->remove($user->getProfile());
            }
            $em->flush();

            $this->get('session')->setFlash('success',
                'The user has been deleted.');
        } catch(Exception $e) {
            $this->get('session')->setFlash('error',
                'The user couldn\'t be deleted.');
        }
        return new Response();
    }

    private function addProfileForm(User $user)
    {
        $container = $this->container;
        $profileEntity = $container->getParameter('fom_user.profile_entity');
        $profileFormType = $container->getParameter('fom_user.profile_formtype');
        $profileTemplate = $container->getParameter('fom_user.profile_template');

        if($profileEntity !== null && $profileFormType !== null) {
            if($user->getId()) {
                $profile = $this->getDoctrine()->getRepository($profileEntity)
                    ->find($user->getId());
                if(!$profile) {
                $profile = new $profileEntity();
                }
            } else {
                $profile = new $profileEntity();
            }

            $user->setProfile($profile);

        } else {
            $profileFormType = null;
            $profileTemplate = null;
        }

        return array(
            'formtype' => $profileFormType,
            'template' => $profileTemplate
        );
    }
}

