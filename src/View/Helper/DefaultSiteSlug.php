<?php declare(strict_types=1);

namespace Next\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper to get the default site slug, or the first one.
 *
 * @deprecated Use Common >defaultSite('slug').
 */
class DefaultSiteSlug extends AbstractHelper
{
    /**
     * Return the default site slug, or the first one.
     *
     * @return string
     *
     * @deprecated Use Common ->defaultSite('slug').
     */
    public function __invoke(): ?string
    {
        $view = $this->getView();
        $defaultSite = $view->plugin('defaultSite');
        return $defaultSite('slug');
    }
}
