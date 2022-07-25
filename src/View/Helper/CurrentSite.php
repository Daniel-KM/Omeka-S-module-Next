<?php declare(strict_types=1);

namespace Next\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\SiteRepresentation;

class CurrentSite extends AbstractHelper
{
    /**
     * Get the current site from the view.
     */
    public function __invoke(): ?SiteRepresentation
    {
        return $this->view->site ?? $this->view->site = $this->getView()
            ->getHelperPluginManager()
            ->get('Laminas\View\Helper\ViewModel')
            ->getRoot()
            ->getVariable('site');
    }
}
