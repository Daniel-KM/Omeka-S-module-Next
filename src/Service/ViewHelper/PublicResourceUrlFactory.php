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
        $viewHelpers = $services->get('ViewHelperManager');
        $defaultSite = $viewHelpers->get('defaultSite');
        $userSiteSlugs = $viewHelpers->get('userSiteSlugs');
        return new PublicResourceUrl(
            $defaultSite('id_slug'),
            $userSiteSlugs()
        );
    }
}
