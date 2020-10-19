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
     * @return string
     */
    public function __invoke(): string
    {
        $view = $this->getView();
        $isAdmin = $view->status()->isAdminRequest();
        $ui = $isAdmin ? 'admin' : 'public';
        $session = new Container('Next');
        return isset($session->lastBrowsePage[$ui])
            ? $session->lastBrowsePage[$ui]
            : $view->url($isAdmin ? 'admin/default' : 'site/resource', ['action' => ''], [], true);
    }
}
