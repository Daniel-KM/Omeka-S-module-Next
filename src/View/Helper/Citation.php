<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\View\Helper\AbstractHelper;

class Citation extends AbstractHelper
{
    /**
     * Return a valid citation for this item.
     *
     * Generally follows Chicago Manual of Style note format for webpages.
     * Implementers can use the item_citation filter to return a customized
     * citation.
     *
     * Upgrade of Omeka Classic Item::getCitation().
     *
     * @todo Find a full php library to manage citation. No event is triggered currently.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @return string
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource)
    {
        $citation = '';
        $view = $this->getView();
        $translate = $view->plugin('translate');

        $creators = $resource->value('dcterms:creator', ['all' => true]) ?: array();
        // Strip formatting and remove empty creator elements.
        $creators = array_filter(array_map('strip_tags', $creators));
        if ($creators) {
            switch (count($creators)) {
                case 1:
                    $creator = $creators[0];
                    break;
                case 2:
                    /// Chicago-style item citation: two authors
                    $creator = sprintf($translate('%1$s and %2$s'), $creators[0], $creators[1]);
                    break;
                case 3:
                    /// Chicago-style item citation: three authors
                    $creator = sprintf($translate('%1$s, %2$s, and %3$s'), $creators[0], $creators[1], $creators[2]);
                    break;
                default:
                    /// Chicago-style item citation: more than three authors
                    $creator = sprintf($translate('%s et al.'), $creators[0]);
                    break;
            }
            $citation .= $creator . ', ';
        }

        $title = $resource->displayTitle();
        if ($title) {
            $citation .= '“' . $title . '”, ';
        }

        $site = $view->currentSite();
        if ($site) {
            $citation .= '<em>' . $site->title() . '</em>, ';
        }

        // TODO Use the locale for the citation.
        $accessed = (new \DateTime())->format('j F Y');
        $url = '<span class="citation-url">' . $view->escapeHtml($resource->siteUrl(null, true)) . '</span>';
        /// Chicago-style item citation: access date and URL
        $citation .= sprintf($translate('accessed %1$s, %2$s.'), $accessed, $url);
        return $citation;
    }
}
