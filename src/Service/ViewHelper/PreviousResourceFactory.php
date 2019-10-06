<?php
namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Next\View\Helper\PreviousResource;
use Zend\ServiceManager\Factory\FactoryInterface;

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
