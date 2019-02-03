<?php
namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Next\View\Helper\NextResource;
use Zend\ServiceManager\Factory\FactoryInterface;

class NextResourceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new NextResource(
            $services->get('Omeka\Connection')
        );
    }
}
