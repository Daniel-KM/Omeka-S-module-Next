<?php
namespace Next\View\Helper;

use Doctrine\DBAL\Connection;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Representation\MediaRepresentation;
use Zend\View\Helper\AbstractHelper;

class NextResource extends AbstractHelper
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the public resource immediately following the current one.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @return AbstractResourceEntityRepresentation
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource)
    {
        $resourceName = $resource->resourceName();
        if ($resourceName === 'media') {
            return $this->nextMedia($resource);
        }

        $resourceTypes = [
            'items' => \Omeka\Entity\Item::class,
            'item_sets' => \Omeka\Entity\ItemSet::class,
            'media' => \Omeka\Entity\Media::class,
        ];
        $resourceType = $resourceTypes[$resourceName];

        $conn = $this->connection;
        $qb = $conn->createQueryBuilder()
            ->select('resource.id')
            ->from('resource', 'resource')
            // TODO Manage the visibility.
            ->where('resource.is_public = 1')
            ->andWhere('resource.resource_type = :resourceType')
            ->setParameter(':resourceType', $resourceType)
            ->andWhere('resource.id > :resource_id')
            ->setParameter(':resource_id', $resource->id())
            ->orderBy('resource.id', 'ASC')
            ->setMaxResults(1);

        // TODO Manage next with site pool. No issue with a single site.
        // $site = $this->currentSite();
        // if ($site) {
        // }

        $stmt = $conn->executeQuery($qb, $qb->getParameters(), $qb->getParameterTypes());
        $result = $stmt->fetchColumn();
        if (!$result) {
            return;
        }

        return $this->getView()->api()->read($resourceName, $result)->getContent();
    }

    protected function nextMedia(MediaRepresentation $media)
    {
        /*
        $conn = $this->connection;
        $qb = $conn->createQueryBuilder()
            ->select('media.id')
            ->from('media', 'media')
            ->innerJoin('resource', 'resource')
            // TODO Manage the visibility.
            ->where('resource.is_public = 1')
            ->andWhere('media.position > :media_position')
            // TODO Get the media position.
            ->setParameter(':media_position', $media->position())
            ->andWhere('media.item_id = :item_id')
            ->setParameter(':item_id', $media->item()->id())
            ->orderBy('resource.id', 'ASC')
            ->setMaxResults(1);
        */

        // TODO Use a better way to get the next media.
        $next = false;
        $mediaId = $media->id();
        foreach ($media->item()->media() as $media) {
            if ($next) {
                return $media;
            }
            if ($media->id() === $mediaId) {
                $next = true;
            }
        }
    }
}
