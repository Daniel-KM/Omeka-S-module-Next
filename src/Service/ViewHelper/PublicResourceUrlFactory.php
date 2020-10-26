<?php declare(strict_types=1);

namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Next\View\Helper\PublicResourceUrl;

/**
 * Service factory for the PublicResourceUrlFactory view helper.
 */
class PublicResourceUrlFactory implements FactoryInterface
{
    /**
     * Create and return the PublicResourceUrl view helper.
     *
     * @return PublicResourceUrl
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $defaultSiteId = $services->get('Omeka\Settings')->get('default_site');
        $viewHelpers = $services->get('ViewHelperManager');
        $userSiteSlugs = $viewHelpers->get('userSiteSlugs');
        $defaultSiteSlug = $viewHelpers->get('defaultSiteSlug');
        return new PublicResourceUrl(
            [$defaultSiteId => $defaultSiteSlug()],
            $userSiteSlugs()
        );
    }
}
