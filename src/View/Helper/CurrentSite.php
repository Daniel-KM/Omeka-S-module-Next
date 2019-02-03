<?php
namespace Next\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CurrentSite extends AbstractHelper
{
    /**
     * Get the current site from the view.
     *
     * @return \Omeka\Api\Representation\SiteRepresentation|null
     */
    public function __invoke()
    {
        return $this->getView()
             ->getHelperPluginManager()
             ->get('Zend\View\Helper\ViewModel')
             ->getRoot()
             ->getVariable('site');
    }
}
