<?php

namespace FOM\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;

/**
 * Event listener for adding user profile on the fly
 *
 * @author Christian Wygoda
 */
class UserProfileListener implements EventSubscriber
{
    /** @var string */
    protected $profileEntityName;

    /**
     * @param string $profileEntityName
     */
    public function __construct($profileEntityName)
    {
        $this->profileEntityName = $profileEntityName;
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
        $user = 'FOM\UserBundle\Entity\User';
        $basicProfile = 'FOM\UserBundle\Entity\BasicProfile';

        if ($user == $metadata->getName()) {
            $metadata->mapOneToOne(array(
                'fieldName' => 'profile',
                'targetEntity' => $this->profileEntityName,
                'mappedBy' => 'uid',
                'cascade' => array('persist'),
            ));
        }

        // need to add metadata for the basic profile, else doctrine
        // will whine in many situations
        if ($this->profileEntityName == $metadata->getName() || $basicProfile == $metadata->getName()) {
            $connection = $args->getEntityManager()->getConnection();
            $platform = $connection->getDatabasePlatform();
            $uidColname = $connection->quoteIdentifier('uid');
            if ($platform instanceof OraclePlatform) {
                $uidColname = strtoupper($uidColname);
            } elseif ($platform instanceof MySqlPlatform) {
                $uidColname = 'uid';
            }
            $metadata->setIdentifier(array('uid'));
            $metadata->setIdGenerator(new AssignedGenerator());
            $metadata->mapOneToOne(array(
                'fieldName' => 'uid',
                'targetEntity' => $user,
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
    }
}
