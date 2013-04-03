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
        $sid = null;
        $mask = null;

        $sidPrefix = '';
        $sidName = '';
        $permissions = array();

        if($ace instanceof Entry) {
            $sid = $ace->getSecurityIdentity();
            $mask = $ace->getMask();
        } elseif(is_array($ace)) {
            $sid = $ace['sid'];
            $mask = $ace['mask'];
        }

        $sidString = '';
        if($sid instanceof RoleSecurityIdentity) {
            $sidPrefix = 'r';
            $sidName = $sid->getRole();
            $sidString = sprintf('%s:%s', $sidPrefix, $sidName);
        } elseif($sid instanceof UserSecurityIdentity) {
            $sidPrefix = 'u';
            $sidName = $sid->getUsername();
            $sidClass = $sid->getClass();
            $sidString = sprintf('%s:%s', $sidPrefix, $sidName);
        }
        
        for($i = 1; $i <= 30; $i++) {
                $key = 1 << ($i-1);
                if($mask & $key) {
                    $permissions[$i] = true;
                } else {
                    $permissions[$i] = false;
                }
        }
        
        return array(
            'sid' => $sidString,
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
            $sid = new UserSecurityIdentity($sidParts[1], 'FOM\UserBundle\Entity\User');
        }
        
        $maskBuilder = new MaskBuilder();
        foreach($data['permissions'] as $bit => $permission) {
            if(true === $permission) {
                $maskBuilder->add(1 << ($bit - 1));
            }
        }
        
        return array(
            'sid' => $sid,
            'mask' => $maskBuilder->get());
    }
}
