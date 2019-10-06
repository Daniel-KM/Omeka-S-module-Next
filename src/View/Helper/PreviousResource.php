<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Representation\MediaRepresentation;
use Zend\View\Helper\AbstractHelper;

class PreviousResource extends AbstractHelper
{
    use NextPreviousResourceTrait;

    /**
     * Get the public resource immediately before the current one.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @return AbstractResourceEntityRepresentation|null
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource)
    {
        $resourceName = $resource->resourceName();
        if ($resourceName === 'media') {
            return $this->previousMedia($resource);
        }
        return $this->previousOrNextResource($resource, '<');
    }

    protected function previousMedia(MediaRepresentation $media)
    {
        /*
        $conn = $this->connection;
        $qb = $conn->createQueryBuilder()
            ->select('media.id')
            ->from('media', 'media')
            ->innerJoin('resource', 'resource')
            // TODO Manage the visibility.
            ->where('resource.is_public = 1')
            ->andWhere('media.position < :media_position')
            // TODO Get the media position.
            ->setParameter(':media_position', $media->position())
            ->andWhere('media.item_id = :item_id')
            ->setParameter(':item_id', $media->item()->id())
            ->orderBy('resource.id', 'ASC')
            ->setMaxResults(1);
        */

        // TODO Use a better way to get the previous media.
        $previous = null;
        $mediaId = $media->id();
        foreach ($media->item()->media() as $media) {
            if ($media->id() === $mediaId) {
                return $previous;
            }
            $previous = $media;
        }
    }
}
