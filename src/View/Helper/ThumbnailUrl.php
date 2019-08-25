<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\AbstractRepresentation;
use Omeka\Api\Representation\AssetRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
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
        if ($representation instanceof SitePageRepresentation) {
            $representation = $this->thumbnailUrlPage($representation);
            if (!$representation) {
                return;
            }
        }

        if ($representation instanceof AssetRepresentation) {
            return $representation->assetUrl();
        }

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

    protected function thumbnailUrlPage(SitePageRepresentation $page)
    {
        $view = $this->getView();
        $api = $view->plugin('api');

        $layoutsWithResource = [
            // 'html',
            // Core.
            'media',
            'browsePreview',
            'itemShowcase',
            'itemShowCase',
            'itemWithMetadata',
            // BlockPlus.
            'assets',
            'resourceText',
        ];

        $blocks = $page->blocks();
        foreach ($blocks as $block) {
            $layout = $block->layout();
            if (in_array($layout, $layoutsWithResource)) {
                switch ($layout) {
                    case 'media':
                    case 'itemShowcase':
                    case 'itemShowCase':
                    case 'itemWithMetadata':
                    case 'resourceText':
                        /** @var \Omeka\Api\Representation\SiteBlockAttachmentRepresentation $attachement */
                        $attachments = $block->attachments();
                        if (empty($attachments)) {
                            break;
                        }
                        $attachment = reset($attachments);
                        return $attachment->media() ?: $attachment->item();

                    case 'browsePreview':
                        $resourceType = $block->dataValue('resource_type', 'items');
                        $query = [];
                        parse_str(ltrim($block->dataValue('query'), '? '), $query);
                        $site = $block->page()->site();
                        if ($view->siteSetting('browse_attached_items', false)) {
                            $query['site_attachments_only'] = true;
                        }
                        $query['site_id'] = $site->id();
                        if (!isset($query['sort_by'])) {
                            $query['sort_by'] = 'created';
                        }
                        if (!isset($query['sort_order'])) {
                            $query['sort_order'] = 'desc';
                        }
                        $representation = $api->searchOne($resourceType, $query)->getContent();
                        if ($representation) {
                            return $representation;
                        }
                        break;

                    case 'assets':
                        $assets = $block->dataValue('assets', []);
                        foreach ($assets as $assetData) {
                            if (empty($assetData['asset'])) {
                                continue;
                            }
                            try {
                                /** @var \Omeka\Api\Representation\AssetRepresentation $asset */
                                $asset = $api->read('assets', $assetData['asset'])->getContent();
                                return $asset;
                            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                            }
                        }
                        break;
                }
            }
        }
    }
}
