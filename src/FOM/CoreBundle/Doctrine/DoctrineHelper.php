<?php
namespace FOM\CoreBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\SqliteSchemaManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DoctrineHelper
 *
 * @author Andriy Oblivantsev <eslider@gmail.com>
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
        /** @var Connection $connection */
        /** @var ClassMetadata $classMetadata */
        $doctrine      = $container->get('doctrine');
        $manager       = $doctrine->getManager();
        $schemaTool    = new SchemaTool($doctrine->getManager());
        $connection    = $doctrine->getConnection();
        $schemaManager = $connection->getSchemaManager();
        $classMetadata = $manager->getClassMetadata($className);

        if ($force || !$schemaManager->tablesExist($classMetadata->getTableName())) {

            if ($schemaManager instanceof SqliteSchemaManager) {

                $columns     = array();
                $identifiers = $classMetadata->getIdentifierColumnNames();
                foreach ($classMetadata->getFieldNames() as $fieldName) {
                    $fieldMapping = $classMetadata->getFieldMapping($fieldName);
                    $columnSql    = $fieldMapping["fieldName"];
                    switch ($fieldMapping["type"]) {
                        case 'integer':
                            $columnSql .= " INTEGER ";
                            break;

                        case 'real':
                        case 'double':
                        case 'float':
                            $columnSql .= " REAL ";
                            break;

                        case 'datetime':
                        case 'date':
                        case 'boolean':
                            $columnSql .= " INTEGER ";
                            break;

                        case 'blob':
                        case 'file':
                            $columnSql .= " BLOB ";
                            break;

                        default:
                            $columnSql .= " TEXT ";
                    }

                    // PRIMARY KEY
                    in_array($fieldName, $identifiers) && $columnSql .= "PRIMARY KEY ";
                    $columnSql .= $fieldMapping["nullable"] ? "NULL " : "NOT NULL ";
                    $columns[] = $columnSql;
                }
                $sql       = 'CREATE TABLE IF NOT EXISTS ' . $classMetadata->getTableName() . '( ' . implode(",\n", $columns) . ')';
                $statement = $connection->query($sql);
            } else {
                $schemaTool->updateSchema(array($classMetadata), true);
            }
        }
    }
}