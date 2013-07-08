<?php

namespace FOM\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;


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
     * @param  Group|null $id
     * @return string
     */
    public function transform($id)
    {
        if (!$id)
        {
            return null;
        }

        $group = $this->om->getRepository('FOMUserBundle:Group')
                ->findOneBy(array('id' => $id));

        if (null === $group || !$group instanceof Group)
        {
            throw new TransformationFailedException(sprintf(
                    'A Group with id "%s" does not exist!', $id
            ));
        }

        return $group;
    }

    /**
     * Transforms a string (id) to an object (group).
     *
     * @param  string $id
     * @return Group|null
     * @throws TransformationFailedException if object (group) is not found.
     */
    public function reverseTransform($group)
    {
        if (null === $group || !$group instanceof Group) return "";
        else return (string) $group->getId();
    }

}
