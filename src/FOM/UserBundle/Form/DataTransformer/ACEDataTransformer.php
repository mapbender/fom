<?php

namespace FOM\UserBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class ACEDataTransformer implements DataTransformerInterface
{
    /**
     * Transforms an single ACE to an object suitable for ACEType
     *
     * @param ace $ace
     * @return object
     */
    public function transform($ace)
    {
        $securityIdentity = null;
        $mask = null;

        $sidPrefix = '';
        $sidName = '';
        $permissions = array();

        if($ace instanceof Entry) {
            $securityIdentity = $ace->getSecurityIdentity();
            $mask = $ace->getMask();
        }
        
        $sid = '';
        if($securityIdentity instanceof RoleSecurityIdentity) {
            $sidPrefix = 'r';
            $sidName = $securityIdentity->getRole();
            $sid = sprintf('%s:%s', $sidPrefix, $sidName);
        } elseif($securityIdentity instanceof UserSecurityIdentity) {
            $sidPrefix = 'u';
            $sidName = $securityIdentity->getUsername();
            $sidClass = $securityIdentity->getClass();
            $sid = sprintf('%s:%s:%s', $sidPrefix, $sidClass, $sidName);
        }
        

        for($i = 0; $i <= 30; $i++) {
                $key = 1 << $i;
                $permissions['mask_' . $key] = ($mask & ($key) ? true : false);
        }

        return array(
            'sid' => $sid,
            'permissions' => $permissions);
    }

    /**
     * Transforms an ACEType result into an ACE
     *
     * @param object $data
     * @return ace
     */
    public function reverseTransform($data)
    {
        $sidParts = explode(':', $data['sid']);
        if(strtoupper($sidParts[0]) == 'R') {
            $sid = new RoleSecurityIdentity($sidParts[1]);
        } else {
            $sid = new UserSecurityIdentity($sidParts[1], $sidParts[2]);
        }
        
        $maskBuilder = new MaskBuilder();
        foreach($data['permissions'] as $key => $enabled) {
            if(!$enabled) {
                continue;
            }

            $permission = intval(substr($key, 5));
            $maskBuilder->add($permission);
        }

        return array(
            'sid' => $sid,
            'mask' => $maskBuilder->get());
    }
}
