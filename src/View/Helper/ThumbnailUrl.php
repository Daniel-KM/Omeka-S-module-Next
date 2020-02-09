<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\AbstractRepresentation;
use Omeka\Api\Representation\AssetRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SiteRepresentation;
use Zend\View\Helper\AbstractHelper;

/**
 * View helper to get a thumbnail url.
 */
class ThumbnailUrl extends AbstractHelper
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
        } elseif ($representation instanceof SiteRepresentation) {
            $representation = $this->thumbnailUrlSite($representation);
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

    protected function thumbnailUrlSite(SiteRepresentation $site)
    {
        $view = $this->getView();
        $api = $view->plugin('api');

        // First media from pages in the order of the navigation.
        $pages = $site->linkedPages();
        foreach ($pages as $page) {
            $representation = $this->thumbnailUrlPage($page);
            if ($representation) {
                return $representation;
            }
        }

        // Any other page in the site.
        $pages = $site->notLinkedPages();
        foreach ($pages as $page) {
            $representation = $this->thumbnailUrlPage($page);
            if ($representation) {
                return $representation;
            }
        }

        // Any media in the site.
        return $api->searchOne('media', ['site_id' => $site->id()])->getContent();
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
            'pageMetadata',
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

                    // TODO Always use the page metadata cover if there is one, even if it's not the first block.
                    case 'pageMetadata':
                        $asset = $block->dataValue('cover');
                        if ($asset) {
                            try {
                                /** @var \Omeka\Api\Representation\AssetRepresentation $asset */
                                return $api->read('assets', $asset)->getContent();
                            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                            }
                        }
                        // no break;
                    case 'assets':
                        $assets = $block->dataValue('assets', []);
                        foreach ($assets as $assetData) {
                            if (empty($assetData['asset'])) {
                                continue;
                            }
                            try {
                                /** @var \Omeka\Api\Representation\AssetRepresentation $asset */
                                return $api->read('assets', $assetData['asset'])->getContent();
                            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                            }
                        }
                        break;
                }
            }
        }
    }
}
