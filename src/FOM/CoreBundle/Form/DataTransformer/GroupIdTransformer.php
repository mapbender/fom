<?php

namespace FOM\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use FOM\UserBundle\Entity\Group;

/**
 * 
 */
class GroupIdTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (group) to a string (id).
     *
     * @param  string|string[]|null $id group ids
     * @return Group|ArrayCollection|Group[]|null
     */
    public function transform($id)
    {
        if (null === $id) {
            return null;
        } else if (is_string($id)) {
            /** @var Group|null $entity */
            $entity = $this->om->getRepository('FOMUserBundle:Group')
                ->findOneBy(array('id' => $id));
            return $entity;
        } elseif (is_array($id)) {
            if (count($id) === 0) {
                return null;
            }
            $result = new ArrayCollection();
            foreach($id as $value) {
                $group = $this->om->getRepository('FOMUserBundle:Group')
                    ->findOneBy(array('id' => $value));
                if ($group !== null) {
                    $result->add($group);
                }
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Transforms a string (id) to an object (group).
     *
     * @param  Group|ArrayCollection|Group[]|null $group
     * @return string|string[]
     * @throws TransformationFailedException if object (group) is not found.
     */
    public function reverseTransform($group)
    {
        if(null === $group) {
            return "";
        } elseif ($group instanceof Group) {
            return (string)$group->getId();
        } elseif ($group instanceof ArrayCollection) {
            $result = array();
            foreach($group as $value) {
                $result[] = (string)$value->getId();
            }
            return $result;
        }
        else return "";
    }

}
