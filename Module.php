<?php
namespace Next;

use Omeka\Module\AbstractModule;

/**
 * Next
 *
 * Allows to use some new features of the next release of Omeka S in the stable
 * release.
 *
 * @copyright Daniel Berthereau, 2018
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
