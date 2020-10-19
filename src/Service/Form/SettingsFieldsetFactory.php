<?php declare(strict_types=1);

namespace Next\Service\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Next\Form\SettingsFieldset;

class SettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new SettingsFieldset(null, $options);
        $element->setSettings($services->get('Omeka\Settings'));
        return $element;
    }
}
