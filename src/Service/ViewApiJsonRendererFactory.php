<?php declare(strict_types=1);

namespace Next\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Next\View\Renderer\ApiJsonRenderer;

class ViewApiJsonRendererFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ApiJsonRenderer(
            $services->get('EventManager')
        );
    }
}
