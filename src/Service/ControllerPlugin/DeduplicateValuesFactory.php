<?php
namespace Next\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Next\Mvc\Controller\Plugin\DeduplicateValues;
use Zend\ServiceManager\Factory\FactoryInterface;

class DeduplicateValuesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedNamed, array $options = null)
    {
        return new DeduplicateValues(
            $services->get('Omeka\EntityManager'),
            $services->get('Omeka\Logger')
        );
    }
}
