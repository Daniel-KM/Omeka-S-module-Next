<?php declare(strict_types=1);

namespace Next\View\Helper;

use Laminas\Session\Container;
use Laminas\View\Helper\AbstractHelper;

class LastBrowsePage extends AbstractHelper
{
    /**
     * Get the last browse page.
     *
     * It allows to go back to the last search result page after browsing.
     *
     * @param string|null $default The default page to go back. If not set,
     * go to the resource browse page.
     */
    public function __invoke(?string $default = null): string
    {
        $view = $this->getView();
        $isAdmin = $view->status()->isAdminRequest();
        $ui = $isAdmin ? 'admin' : 'public';
        $session = new Container('Next');
        if (empty($session->lastBrowsePage[$ui])) {
            return $default
                ?: $view->url($isAdmin ? 'admin/default' : 'site/resource', ['action' => ''], [], true);
        }
        return $session->lastBrowsePage[$ui];
    }
}
