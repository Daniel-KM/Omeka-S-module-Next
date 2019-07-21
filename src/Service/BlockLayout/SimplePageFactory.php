<?php
namespace Next\Service\BlockLayout;

use Interop\Container\ContainerInterface;
use Next\Site\BlockLayout\SimplePage;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * @deprecated Use the same feature in module BlockPlus.
 */
class SimplePageFactory implements FactoryInterface
{
    /**
     * Create the SimplePage block layout service.
     *
     * @return SimplePage
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new SimplePage(
            $services->get('Omeka\ApiManager')
        );
    }
}
