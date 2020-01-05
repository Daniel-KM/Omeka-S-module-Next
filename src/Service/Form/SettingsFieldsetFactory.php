<?php
namespace Next\Service\Form;

use Interop\Container\ContainerInterface;
use Next\Form\SettingsFieldset;
use Zend\ServiceManager\Factory\FactoryInterface;

class SettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new SettingsFieldset(null, $options);
        $element->setSettings($services->get('Omeka\Settings'));
        return $element;
    }
}
