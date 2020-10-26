<?php declare(strict_types=1);

namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Next\View\Helper\UserSiteSlugs;

/**
 * Service factory to get the user site slugs.
 */
class UserSiteSlugsFactory implements FactoryInterface
{
    /**
     * Create and return the UserSiteSlugs view helper.
     *
     * @return UserSiteSlugs
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $userSiteIds = $services->get('Omeka\Settings\User')->get('default_item_sites', []);
        $slugs = empty($userSiteIds)
            ? []
            : $services->get('Omeka\ApiManager')->search('sites', ['id' => $userSiteIds], ['initialize' => false, 'returnScalar' => 'slug'])->getContent();
        return new UserSiteSlugs($slugs);
    }
}
