<?php declare(strict_types=1);

namespace Next\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceRepresentation;

/**
 * View helper to return the url to the public default site page of a resource.
 */
class PublicResourceUrl extends AbstractHelper
{
    /**
     * @var string[]
     */
    protected $defaultSiteSlug;

    /**
     * @var string[]
     */
    protected $userSiteSlugs;

    /**
     * Construct the helper.
     *
     * @param string[] $defaultSiteSlug
     * @param string[] $userSiteSlugs
     */
    public function __construct(array $defaultSiteSlug, array $userSiteSlugs)
    {
        $this->defaultSiteSlug = $defaultSiteSlug;
        $this->userSiteSlugs = $userSiteSlugs;
    }

    /**
     * Return the url to the public default site page of a resource.
     *
     * @uses AbstractResourceRepresentation::siteUrl()
     *
     * @param AbstractResourceRepresentation $resource
     * @param bool $canonical Whether to return an absolute URL
     * @return string May be an empty string if there is no site.
     */
    public function __invoke(AbstractResourceRepresentation $resource, $canonical = false): string
    {
        if (method_exists($resource, 'sites')) {
            $sites = $resource->sites();
        } elseif ($resource instanceof \Omeka\Api\Representation\MediaRepresentation) {
            $item = $resource->item();
            $sites = $item->sites();
        } else {
            $sites = [];
        }

        if (count($sites)) {
            $intersectSites = array_intersect_key($sites, $this->userSiteSlugs);
            if (count($intersectSites)) {
                $site = reset($intersectSites);
                return $resource->siteUrl($site->slug(), $canonical);
            }
            if (isset($sites[key($this->defaultSiteSlug)])) {
                return $resource->siteUrl(reset($this->defaultSiteSlug), $canonical);
            }
            $site = reset($sites);
            return $resource->siteUrl($site->slug(), $canonical);
        }

        // Manage the case where there is no site.
        $slug = count($this->userSiteSlugs) ? reset($this->userSiteSlugs) : reset($this->defaultSiteSlug);
        return $slug
            ? (string) $resource->siteUrl($slug, $canonical)
            : '';
    }
}
