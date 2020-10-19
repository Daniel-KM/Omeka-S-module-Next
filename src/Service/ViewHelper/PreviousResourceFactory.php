<?php declare(strict_types=1);
namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Next\View\Helper\PreviousResource;

class PreviousResourceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $currentSite = $services->get('ControllerPluginManager')->get('currentSite');
        return new PreviousResource(
            $services->get('Omeka\ApiAdapterManager'),
            $currentSite()
        );
    }
}
