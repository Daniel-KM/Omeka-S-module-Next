<?php
namespace Next\View\Helper;

use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;

class LastBrowsePage extends AbstractHelper
{
    /**
     * Get the last browse page.
     *
     * It allows to go back to the last search result page after browsing.
     *
     * @return string
     */
    public function __invoke()
    {
        $view = $this->getView();
        $params = $view->params();
        $isAdmin = (bool) $params->fromRoute('__ADMIN__');
        $ui = $isAdmin ? 'admin' : 'public';
        $session = new Container('Next');
        return isset($session->lastBrowsePage[$ui])
            ? $session->lastBrowsePage[$ui]
            : $view->url($isAdmin ? 'admin/default' : 'site/resource', ['action' => ''], [], true);
    }
}
