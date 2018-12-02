<?php
namespace Next\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Next\Mvc\Controller\Plugin\TrimValues;
use Zend\ServiceManager\Factory\FactoryInterface;

class TrimValuesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedNamed, array $options = null)
    {
        return new TrimValues(
            $services->get('Omeka\EntityManager'),
            $services->get('Omeka\Logger')
        );
    }
}
