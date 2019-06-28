<?php


namespace FOM\UserBundle\Controller;


use Doctrine\ORM\EntityManagerInterface;
use FOM\UserBundle\Component\AclManager;
use FOM\UserBundle\Component\UserHelperService;
use FOM\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class UserControllerBase extends Controller
{
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
     * @return string|null
     */
    protected function getEmailFromAdress()
    {
        return $this->container->getParameter('fom_user.mail_from_address');
    }

    /**
     * @return UserHelperService
     */
    protected function getUserHelper()
    {
        /** @var UserHelperService $service */
        $service = $this->get('fom.user_helper.service');
        return $service;
    }

    /**
     * @return MutableAclProviderInterface
     */
    protected function getAclProvider()
    {
        /** @var MutableAclProviderInterface $service */
        $service = $this->get('security.acl.provider');
        return $service;
    }

    /**
     * @return AclManager
     */
    protected function getAclManager()
    {
        /** @var AclManager $service */
        $service = $this->get('fom.acl.manager');
        return $service;
    }

    /**
     * @param \DateTime $startTime
     * @param string $timeInterval
     * @return bool
     * @throws \Exception
     */
    protected function checkTimeInterval($startTime, $timeInterval)
    {
        $endTime = new \DateTime();
        $endTime->sub(new \DateInterval(sprintf("PT%dH", $timeInterval)));
        return !($startTime < $endTime);
    }

    protected function translate($x)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');
        return $translator->trans($x);
    }

    /**
     * @param User $user
     * @return Response
     */
    protected function tokenExpired($user)
    {
        $form = $this->createForm('form', null, array(
            'action' => $this->generateUrl('fom_user_password_tokenreset', array(
                'token' => $user->getResetToken(),
            )),
        ));
        return $this->render('@FOMUser/Login/error-tokenexpired.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }
}
