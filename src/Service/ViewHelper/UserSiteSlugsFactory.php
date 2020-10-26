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
        $user = $services->get('Omeka\AuthenticationService')->getIdentity();
        if ($user) {
            /** @var \Omeka\Settings\UserSettings $userSettings */
            $userSettings = $services->get('Omeka\Settings\User');
            // In some cases (public non-standard urls), the user may be not set.
            $userSettings->setTargetId($user->getId());
            $userSiteIds = $userSettings->get('default_item_sites', []);
            $slugs = empty($userSiteIds)
                ? []
                : $services->get('Omeka\ApiManager')->search('sites', ['id' => $userSiteIds], ['initialize' => false, 'returnScalar' => 'slug'])->getContent();
        } else {
            $slugs = [];
        }
        return new UserSiteSlugs($slugs);
    }
}
