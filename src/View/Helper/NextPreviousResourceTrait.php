<?php
namespace Next\View\Helper;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractResourceEntityAdapter;
use Omeka\Api\Adapter\Manager as AdapterManager;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Representation\SiteRepresentation;

trait NextPreviousResourceTrait
{
    /**
     * @var AdapterManager
     */
    protected $adapterManager;

    /**
     * @var SiteRepresentation
     */
    protected $site;

    /**
     * @var AbstractResourceEntityAdapter
     */
    protected $adapter;

    public function __construct(AdapterManager $adapterManager, SiteRepresentation $site = null)
    {
        $this->adapterManager = $adapterManager;
        $this->site = $site;
    }

    /**
     * Get the public resource immediately before or following the current one.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param string $lowerOrGreaterThan "<" or ">".
     * @return AbstractResourceEntityRepresentation|null
     */
    protected function previousOrNextResource(AbstractResourceEntityRepresentation $resource, $lowerOrGreaterThan)
    {
        $resourceName = $resource->resourceName();

        /** @var \Omeka\Api\Adapter\AbstractResourceEntityAdapter $adapter */
        $this->adapter = $this->adapterManager->get($resourceName);

        $resourceTypes = [
            'items' => \Omeka\Entity\Item::class,
            'item_sets' => \Omeka\Entity\ItemSet::class,
            'media' => \Omeka\Entity\Media::class,
        ];
        $resourceType = $resourceTypes[$resourceName];

        // Visibility is automatically managed.

        $isOldOmeka = \Omeka\Module::VERSION < 2;
        $alias = $isOldOmeka ? $this->adapter->getEntityClass() : 'omeka_root';

        $entityManager = $this->adapter->getEntityManager();
        $qb = $entityManager->createQueryBuilder()
            ->select($alias . '.id')
            ->from($resourceType, $alias)
            ->andWhere($alias . '.id ' . $lowerOrGreaterThan . ' :resource_id')
            ->setParameter(':resource_id', $resource->id())
            ->orderBy($alias . '.id', $lowerOrGreaterThan === '<' ? 'DESC' : 'ASC')
            ->setMaxResults(1);

        if ($this->site) {
            switch ($resourceName) {
                case 'items':
                    $this->filterItemsBySite($qb);
                    break;
                case 'item_sets':
                    $this->filterItemSetsBySite($qb);
                    break;
                case 'media':
                default:
                    break;
            }
        }

        $result = $qb->getQuery()->getResult();
        return $result
            ? $this->getView()->api()->read($resourceName, $result[0]['id'])->getContent()
            : null;
    }

    /**
     * Filter a query for items by site.
     *
     * @see \Omeka\Api\Adapter\ItemAdapter::buildQuery()
     * @param QueryBuilder $qb
     */
    protected function filterItemsBySite(QueryBuilder $qb)
    {
        if (!$this->adapter || !$this->site) {
            return;
        }

        $params = $this->site->itemPool();
        if (!is_array($params)) {
            $params = [];
        }
        // Avoid potential infinite recursion.
        unset($params['site_id']);

        $isOldOmeka = \Omeka\Module::VERSION < 2;
        $alias = $isOldOmeka ? 'Omeka\Entity\Item' : 'omeka_root';

        $this->adapter->buildQuery($qb, $params);

        if ($this->getView()->siteSetting('browse_attached_items', false)) {
            $siteBlockAttachmentsAlias = $this->adapter->createAlias();
            $qb->innerJoin(
                $alias . '.siteBlockAttachments',
                $siteBlockAttachmentsAlias
            );
            $sitePageBlockAlias = $this->adapter->createAlias();
            $qb->innerJoin(
                "$siteBlockAttachmentsAlias.block",
                $sitePageBlockAlias
            );
            $sitePageAlias = $this->adapter->createAlias();
            $qb->innerJoin(
                "$sitePageBlockAlias.page",
                $sitePageAlias
            );
            $siteAlias = $this->adapter->createAlias();
            $qb->innerJoin(
                "$sitePageAlias.site",
                $siteAlias
            );
            $qb->andWhere($qb->expr()->eq(
                "$siteAlias.id",
                $this->adapter->createNamedParameter($qb, $this->site->id()))
            );
        }
    }

    /**
     * Filter a query for item sets by site.
     *
     * @see \Omeka\Api\Adapter\ItemSetAdapter::buildQuery()
     * @param QueryBuilder $qb
     */
    protected function filterItemSetsBySite(QueryBuilder $qb)
    {
        if (!$this->adapter || !$this->site) {
            return;
        }

        $isOldOmeka = \Omeka\Module::VERSION < 2;
        $alias = $isOldOmeka ? 'Omeka\Entity\ItemSet' : 'omeka_root';

        $siteItemSetsAlias = $this->adapter->createAlias();
        $qb->innerJoin(
            $alias . '.siteItemSets',
            $siteItemSetsAlias
        );
        $qb->andWhere($qb->expr()->eq(
            "$siteItemSetsAlias.site",
            $this->adapter->createNamedParameter($qb, $this->site->id()))
        );
        $qb->addOrderBy("$siteItemSetsAlias.position", 'ASC');
    }
}
