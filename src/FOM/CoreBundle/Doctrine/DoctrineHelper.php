<?php
namespace FOM\CoreBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Andriy Oblivantsev <eslider@gmail.com>
 * @deprecated remove in FOM v3.3; never update your schema in a live session. Always use app/console doctrine:schema:update.
 */
class DoctrineHelper
{
    /**
     * Check and create table if not exists
     *
     * @param ContainerInterface $container ContainerInterface Container
     * @param                    $className String  Name of the ORM class
     * @param bool               $force     bool    Force update table
     */
    public static function checkAndCreateTableByEntityName(ContainerInterface $container, $className, $force = false)
    {
        /** @var Registry $doctrine */
        $doctrine      = $container->get('doctrine');
        /** @var EntityManagerInterface $manager */
        $manager       = $doctrine->getManager();
        $schemaTool    = new SchemaTool($manager);
        $schemaManager = $manager->getConnection()->getSchemaManager();
        $classMetadata = $manager->getClassMetadata($className);
        if ($force || !$schemaManager->tablesExist(array($classMetadata->getTableName()))) {
            $schemaTool->updateSchema(array($classMetadata), true);
        }
    }
}
