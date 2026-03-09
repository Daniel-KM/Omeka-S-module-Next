<?php declare(strict_types=1);

namespace Next;

use Omeka\Module\AbstractModule;

/**
 * @deprecated All features moved to other modules.
 * @copyright Daniel Berthereau, 2018-2026
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    public function upgrade(
        $oldVersion,
        $newVersion,
        \Laminas\ServiceManager\ServiceLocatorInterface $serviceLocator
    ) {
        $services = $serviceLocator;
        require_once __DIR__ . '/data/scripts/upgrade.php';
    }
}
