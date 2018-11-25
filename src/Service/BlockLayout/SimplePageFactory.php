<?php
namespace Next\Service\BlockLayout;

use Interop\Container\ContainerInterface;
use Next\Site\BlockLayout\SimplePage;
use Zend\ServiceManager\Factory\FactoryInterface;

class SimplePageFactory implements FactoryInterface
{
    /**
     * Create the SimplePage block layout service.
     *
     * @return SimplePage
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $controllerPluginManager = $services->get('ControllerPluginManager');
        return new SimplePage(
            $services->get('FormElementManager'),
            $services->get('Config')['next']['block_settings']['simplePage'],
            $controllerPluginManager->get('api')
        );
    }
}
