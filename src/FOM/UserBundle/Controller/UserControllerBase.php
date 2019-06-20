<?php


namespace FOM\UserBundle\Controller;


use FOM\UserBundle\Component\AclManager;
use FOM\UserBundle\Component\UserHelperService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class UserControllerBase extends Controller
{
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
}
