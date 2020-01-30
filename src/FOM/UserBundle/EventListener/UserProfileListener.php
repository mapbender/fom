<?php

namespace FOM\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Event listener for adding user profile on the fly
 *
 * @author Christian Wygoda
 */
class UserProfileListener implements EventSubscriber
{
    /** @var string */
    protected $profileEntityName;
    /** @var string */
    protected $userEntityName;

    /**
     * @param string $userEntityClass
     * @param string $profileEntityClass
     */
    public function __construct($userEntityClass, $profileEntityClass)
    {
        $this->userEntityName = $userEntityClass;
        $this->profileEntityName = $profileEntityClass;
    }

    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $metadata = $args->getClassMetadata();
        $metadataClass = $metadata->getName();
        if ($this->isUserEntity($metadataClass)) {
            $this->patchUserEntity($metadata);
        } elseif ($this->isProfileEntity($metadataClass)) {
            $platform = $args->getEntityManager()->getConnection()->getDatabasePlatform();
            $this->patchProfileEntity($metadata, $platform);
        }
    }

    protected function patchUserEntity(ClassMetadata $metadata)
    {
        $metadata->mapOneToOne(array(
            'fieldName' => 'profile',
            'targetEntity' => $this->profileEntityName,
            'mappedBy' => 'uid',
            'cascade' => array('persist'),
        ));
    }

    protected function patchProfileEntity(ClassMetadata $metadata, AbstractPlatform $platform)
    {
        /** @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#quoting-reserved-words */
        $uidColname = $platform->quoteIdentifier('uid');
        if ($platform instanceof OraclePlatform) {
            // why..?
            $uidColname = strtoupper($uidColname);
        }
        $metadata->setIdentifier(array('uid'));
        $metadata->setIdGenerator(new AssignedGenerator());
        $metadata->mapOneToOne(array(
            'fieldName' => 'uid',
            'targetEntity' => $this->userEntityName,
            'inversedBy' => 'profile',
            'id' => true,
            'joinColumns' => array(
                array(
                    'name' => $uidColname,
                    'referencedColumnName' => 'id',
                ),
            ),
        ));
    }

    /**
     * @param string $className
     * @return boolean
     */
    protected function isUserEntity($className)
    {
        // this is checked for ALL entity types, so do as few instance checks as possible
        $defaultClass = 'FOM\UserBundle\Entity\User';
        if (\is_a($className, $defaultClass, true)) {
            return true;
        } elseif ($this->userEntityName !== $defaultClass) {
            return \is_a($className, $this->userEntityName);
        } else {
            return false;
        }
    }

    /**
     * @param string $className
     * @return boolean
     */
    protected function isProfileEntity($className)
    {
        // this is checked for ALL entity types, so do as few instance checks as possible
        $defaultClass = 'FOM\UserBundle\Entity\BasicProfile';
        if (\is_a($className, $defaultClass, true)) {
            return true;
        } elseif ($this->profileEntityName !== $defaultClass) {
            return \is_a($className, $this->profileEntityName);
        } else {
            return false;
        }
    }
}
