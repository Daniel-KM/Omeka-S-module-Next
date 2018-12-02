<?php
namespace Next\Mvc\Controller\Plugin;

use Doctrine\ORM\EntityManager;
use Zend\Log\LoggerInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class DeduplicateValues extends AbstractPlugin
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The logger is required, because there is no controller during job.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Deduplicate specified or all resource values.
     *
     * @param array|null $resourceIds Passing an empty array means that there is
     * no ids to process. To process all values, pass a null or no argument.
     * @return int Number of deduplicated values.
     */
    public function __invoke(array $resourceIds = null)
    {
        if (!is_null($resourceIds)) {
            $resourceIds = array_filter(array_map('intval', $resourceIds));
            if (!count($resourceIds)) {
                return 0;
            }
        }

        // For large base, a temporary table is prefered to speed process.
        $connection = $this->entityManager->getConnection();
        $logger = $this->logger;

        $query = is_null($resourceIds)
            ? $this->prepareQuery()
            : $this->prepareQueryForResourceIds($resourceIds);

        $deduplicated = $connection->exec($query);
        $logger->info(sprintf('Deduplicated %d values.', $deduplicated));
        return $deduplicated;
    }

    protected function prepareQuery()
    {
        return <<<'SQL'
DROP TABLE IF EXISTS value_temporary;
CREATE TEMPORARY TABLE value_temporary (id INT, PRIMARY KEY (id))
AS
    SELECT id
    FROM value
    GROUP BY resource_id, property_id, value_resource_id, type, lang, value, uri, is_public;
DELETE v FROM value v
LEFT JOIN value_temporary value_temporary
    ON value_temporary.id = v.id
WHERE value_temporary.id IS NULL;
DROP TABLE IF EXISTS value_temporary;
SQL;
    }

    protected function prepareQueryForResourceIds(array $resourceIds)
    {
        $idsString = implode(',', $resourceIds);
        return <<<SQL
CREATE TEMPORARY TABLE value_temporary (id INT, PRIMARY KEY (id))
AS
    SELECT id
    FROM value
    WHERE resource_id IN ($idsString)
    GROUP BY resource_id, property_id, value_resource_id, type, lang, value, uri, is_public;
DELETE v FROM value v
    LEFT JOIN value_temporary value_temporary
    ON value_temporary.id = v.id
WHERE resource_id IN ($idsString)
    AND value_temporary.id IS NULL;
DROP TABLE IF EXISTS value_temporary;
SQL;
    }
}
