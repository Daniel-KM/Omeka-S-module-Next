<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\AbstractRepresentation;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * View helper to get a thumbnail url.
 */
class ThumbnailUrl extends AbstractHtmlElement
{
    /**
     * Get a thumbnail url of a representation.
     *
     * The thumbnail may be specified directly, or be the primary media one.
     *
     * @see \Omeka\View\Helper\Thumbnail
     *
     * @param AbstractRepresentation $representation
     * @param string $type
     * @return string
     */
    public function __invoke(AbstractRepresentation $representation, $type = 'square')
    {
        if (version_compare(\Omeka\Module::VERSION, '1.3', '>=')) {
            $thumbnail = $representation->thumbnail();
            if ($thumbnail) {
                return $thumbnail->assetUrl();
            }
        }

        $primaryMedia = $representation->primaryMedia();
        return $primaryMedia
            ? $primaryMedia->thumbnailUrl($type)
            : null;
    }
}
