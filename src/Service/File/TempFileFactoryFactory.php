<?php declare(strict_types=1);

namespace Next\Service\File;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Next\File\TempFileFactory;

class TempFileFactoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new TempFileFactory($services);
    }
}
