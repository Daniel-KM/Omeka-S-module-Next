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
        $params = $this->getView()->params()->fromRoute();
        $ui = $params['__ADMIN__'] ? 'admin' : 'public';
        $session = new Container('Next');
        return isset($session->lastSearch[$ui])
            ? $session->lastSearch[$ui]
            : '';
    }
}
