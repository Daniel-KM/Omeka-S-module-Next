<?php
namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Next\View\Helper\BrowsePreviousNext;
use Zend\ServiceManager\Factory\FactoryInterface;

class BrowsePreviousNextFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new BrowsePreviousNext(
            $services->get('Omeka\ApiAdapterManager'),
            $services->get('Omeka\Connection'),
            $services->get('Omeka\EntityManager')
        );
    }
}
