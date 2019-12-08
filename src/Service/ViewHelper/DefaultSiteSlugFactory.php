<?php
namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Next\View\Helper\DefaultSiteSlug;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Service factory to get the default site slug, or the first site slug.
 *
 * @todo Store the default site as slug instead of id?
 * @todo Set a setting for the default site of the user?
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
            try {
                $response = $api->read('sites', ['id' => $defaultSiteId], [], ['responseContent' => 'resource']);
                $slug = $response->getContent()->getSlug();
            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                $slug = '';
            }
        } else {
            $slugs = $api->search('sites', ['limit' => 1, 'sort_by' => 'id'], ['returnScalar' => 'slug'])->getContent();
            $slug = (string) reset($slugs);
        }
        return new DefaultSiteSlug($slug);
    }
}
