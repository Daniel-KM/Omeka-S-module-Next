<?php declare(strict_types=1);

namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Next\View\Helper\DefaultSiteSlug;

/**
 * Service factory to get the default site slug, or the first site slug.
 */
class DefaultSiteSlugFactory implements FactoryInterface
{
    /**
     * Create and return the DefaultSiteSlug view helper.
     *
     * @return DefaultSiteSlug
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $api = $services->get('Omeka\ApiManager');
        $defaultSiteId = $services->get('Omeka\Settings')->get('default_site');
        if ($defaultSiteId) {
            $slugs = $api->search('sites', ['id' => $defaultSiteId], ['initialize' => false, 'returnScalar' => 'slug'])->getContent();
            $slug = (string) reset($slugs);
        }
        if (empty($slug)) {
            $slugs = $api->search('sites', ['limit' => 1, 'sort_by' => 'id'], ['initialize' => false, 'returnScalar' => 'slug'])->getContent();
            $slug = (string) reset($slugs);
        }
        return new DefaultSiteSlug($slug);
    }
}
