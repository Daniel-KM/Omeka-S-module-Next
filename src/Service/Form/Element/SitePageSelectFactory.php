<?php
namespace Next\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Next\Form\Element\SitePageSelect;
use Zend\ServiceManager\Factory\FactoryInterface;

class SitePageSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new SitePageSelect;
        $element->setApiManager($services->get('Omeka\ApiManager'));
        return $element;
    }
}
