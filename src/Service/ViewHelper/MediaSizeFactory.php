<?php
namespace Next\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Next\View\Helper\MediaSize;
use Zend\ServiceManager\Factory\FactoryInterface;

class MediaSizeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $config = $services->get('Config');
        // Get the local dir, that is not available in the default config.
        $basePath = $config['file_store']['local']['base_path'] ?: OMEKA_PATH . '/files';
        return new MediaSize(
            $basePath
        );
    }
}
