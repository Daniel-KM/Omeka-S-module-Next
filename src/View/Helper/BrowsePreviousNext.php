<?php
namespace Next\View\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Request;
use Zend\EventManager\Event;
use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;

class BrowsePreviousNext extends AbstractHelper
{
    /**
     * @var ApiAdapterManager
     */
    protected $apiAdapterManager;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param ApiAdapterManager $apiAdapterManager
     * @param Connection $connection
     * @param EntityManager $entityManager
     */
    public function __construct(ApiAdapterManager $apiAdapterManager, Connection $connection, EntityManager $entityManager)
    {
        $this->apiAdapterManager = $apiAdapterManager;
        $this->connection = $connection;
        $this->entityManager = $entityManager;
    }

    /**
     * Get the links to previous, next and back of a resource.
     *
     * @todo Check visibility for public front-end.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $options
     * @return string Html code
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource, array $options = [])
    {
        $view = $this->getView();
        $params = $view->params();
        $isAdmin = (bool) $params->fromRoute('__ADMIN__');
        $ui = $isAdmin ? 'admin' : 'public';

        $options += [
            'template' => null,
            'upper' => true,
        ];

        $session = new Container('Next');
        $query = isset($session->lastQuery[$ui]) ? $session->lastQuery[$ui] : [];

        $lastBrowse = $options['upper'] ? $view->lastBrowsePage() : null;
        list($previous, $next) = $this->previousNext($resource, $query);

        $template = empty($options['template']) ? 'common/browse-previous-next' : $options['template'];
        unset($options['template']);

        return $view->partial($template, [
            'resource' => $resource,
            'previous' => $previous,
            'next' => $next,
            'lastBrowse' => $lastBrowse,
            'options' => $options,
        ]);
    }

    protected function previousNext(AbstractResourceEntityRepresentation $resource, array $query)
    {
        $resourceName = $resource->resourceName();

        // In mysql 8, row_number() can be used, not in the default Omeka (5.5.3).

        // A simple solution:
        // The position is useless, we just want to get the previous row:
        // so get field used to order by, then get the value of the resource for
        // this field, then offset -1 and limit 1.
        // This solution is simpler, but manage only the case where there is a
        // simple order, so no order by title, count, etc.

        // So use a full query.

        // TODO Improve the way to get the previous and next resources.
        // But the original query has parameters that should be set in the sql,
        // but the parameters have placeholders in dql, but only "?" in pdo sql
        // (see \Doctrine\ORM\Query\ParserResult::getParameterMappings()),
        // and there seems to be no simple way to get the raw sql with
        // placeholders, or the full list of parameters.
        // So the params are set inside the query, then the position can be
        // retrieved.

        // First step, get the original query, unchanged, without limit.
        $qb = $this->prepareSearch($resourceName, $query)
            ->setMaxResults(null)
            ->setFirstResult(null);

        // Manage visibility.
        // TODO Use standard automatic filter to check visibility.

        // Convert query builder parameters into standard pdo parameters.
        $parameters = [];

        $quote = function($v) {
            if (strpos($v, "'")) {
                // Direct sql quotation uses two single quotes, not a backslash.
                $v = $this->connection->quote($v);
                return str_replace("\'", "''", $v);
            }
            return $this->connection->quote($v);
        };

        foreach ($qb->getParameters()->toArray() as $param) {
            $paramValue = $param->getValue();
            if (is_array($paramValue)) {
                $paramValue = implode(',', array_map($quote, $paramValue));
            } elseif ($param->getType() !== \Doctrine\DBAL\Types\Type::INTEGER) {
                $paramValue = $quote($paramValue);
            }
            $parameters[':' . $param->getName()] = $paramValue;
        }

        // Replace placeholders by true values.
        $dql = $qb->getDQL();
        $dql = str_replace(array_keys($parameters), array_values($parameters), $dql);

        // Convert dql into sql.
        $sqlQuery = $this->entityManager->createQuery($dql)->getSQL();
        $sql = <<<SQL
SELECT y.position
FROM (
    SELECT x.id_0 as id, @rownum := @rownum + 1 AS position
    FROM (
        $sqlQuery
    ) x
    JOIN (SELECT @rownum := 0) row
) y
WHERE y.id = ?;
SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$resource->id()]);
        $position = $stmt->fetchColumn();

        // Second step, get the previous and next resources.
        if (!$position) {
            return [null, null];
        }

        $sql = <<<SQL
SELECT y.position, y.id
FROM (
    SELECT x.id_0 as id, @rownum := @rownum + 1 AS position
    FROM (
        $sqlQuery
    ) x
    JOIN (SELECT @rownum := 0) row
) y
WHERE y.position >= ? AND y.position <= ?
LIMIT 3;
SQL;
        $previous = $position - 1;
        $next = $position + 1;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$previous, $next]);
        $ids = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        $api = $this->getView()->api();
        $previous = isset($ids[$previous])
            ? $api->read($resourceName, $ids[$previous])->getContent()
            : null;
        $next = isset($ids[$next])
            ? $api->read($resourceName, $ids[$next])->getContent()
            : null;
        return [$previous, $next];
    }

    /**
     * Copy of \Omeka\Api\Adapter\AbstractEntityAdapter::search() to get a prepared query builder.
     *
     * @todo Trigger all api manager events (api.execute.pre, etc.).
     *
     * @param string $resourceName
     * @param array $query
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function prepareSearch($resourceName, array $query)
    {
        /** @var \Omeka\Api\Adapter\AbstractResourceEntityAdapter $adapter */
        $adapter = $this->apiAdapterManager->get($resourceName);

        $request = new Request('search', $resourceName);
        $request->setContent($query);

        // Set default query parameters
        $defaultQuery = [
            'page' => null,
            'per_page' => null,
            'limit' => null,
            'offset' => null,
            'sort_by' => null,
            'sort_order' => null,
        ];
        $query += $defaultQuery;
        $query['sort_order'] = strtoupper($query['sort_order']) === 'DESC' ? 'DESC' : 'ASC';

        // Begin building the search query.
        $entityClass = $adapter->getEntityClass();

        $isOldOmeka = \Omeka\Module::VERSION < 2;
        $alias = $isOldOmeka ? $entityClass : 'omeka_root';

        // $adapter->index = 0;
        $qb = $adapter->getEntityManager()
            ->createQueryBuilder()
            ->select($alias)
            ->from($entityClass, $alias);
        $adapter->buildQuery($qb, $query);
        $qb->groupBy("$alias.id");

        // Trigger the search.query event.
        $event = new Event('api.search.query', $adapter, [
            'queryBuilder' => $qb,
            'request' => $request,
        ]);
        $adapter->getEventManager()->triggerEvent($event);

        // Finish building the search query. In addition to any sorting the
        // adapters add, always sort by entity ID.
        $adapter->sortQuery($qb, $query);
        $adapter->limitQuery($qb, $query);
        $qb->addOrderBy("$alias.id", $query['sort_order']);

        if ($isOldOmeka) {
            // Keep only the id.
            // This is not possible with Omeka 2, since \Omeka\Module::searchFulltext()
            // adds a specific select.
            $qb
                ->select($alias . '.id');
        }

        return $qb;
    }
}
