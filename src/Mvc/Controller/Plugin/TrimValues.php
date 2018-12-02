<?php
namespace Next\Mvc\Controller\Plugin;

use Doctrine\ORM\EntityManager;
use Zend\Log\LoggerInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class TrimValues extends AbstractPlugin
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
     * Trim specified or all resource values and remove values that are empty.
     *
     * @param array|null $resourceIds Passing an empty array means that there is
     * no ids to process. To process all values, pass a null or no argument.
     * @return int Number of trimmed values.
     */
    public function __invoke(array $resourceIds = null)
    {
        if (!is_null($resourceIds)) {
            $resourceIds = array_filter(array_map('intval', $resourceIds));
            if (!count($resourceIds)) {
                return 0;
            }
        }

        // Use a direct query: during a post action, data are already flushed.
        // The entity manager can not be used directly, because it doesn't
        // manage regex.
        $connection = $this->entityManager->getConnection();
        $logger = $this->logger;

        $idsString = is_null($resourceIds) ? '' : implode(',', $resourceIds);

        // Sql "trim" is for space " " only, not end of line, new line or tab.
        // So use regexp, allowed at least in mysql 5.5.3 (minimum Omeka).
        // The pattern is a full unicode one.
        $query = <<<'SQL'
UPDATE value v
SET
v.value = NULLIF(REGEXP_REPLACE(v.value, "^[\h\v\s[:blank:][:space:]]+|[\h\v\s[:blank:][:space:]]+$", ""), ""),
v.lang = NULLIF(REGEXP_REPLACE(v.lang, "^[\h\v\s[:blank:][:space:]]+|[\h\v\s[:blank:][:space:]]+$", ""), ""),
v.uri = NULLIF(REGEXP_REPLACE(v.uri, "^[\h\v\s[:blank:][:space:]]+|[\h\v\s[:blank:][:space:]]+$", ""), "")
SQL;
        if ($idsString) {
            $query .= "\n" . <<<SQL
WHERE v.resource_id IN ($idsString)
SQL;
        }

        $trimmed = $connection->exec($query);
        $logger->info(sprintf('Trimmed %d values.', $trimmed));

        // Remove empty values, even if there is a language.
        $query = <<<'SQL'
DELETE FROM value
WHERE value_resource_id IS NULL
AND value IS NULL
AND uri IS NULL
SQL;
        if ($idsString) {
            $query .= "\n" . <<<SQL
AND resource_id IN ($idsString)
SQL;
        }
        $deleted = $connection->exec($query);
        $logger->info(sprintf('Removed %d empty string values after trimming.', $deleted));

        return $trimmed;
    }
}
