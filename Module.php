<?php declare(strict_types=1);

namespace Next;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Session\Container;

/**
 * Next
 *
 * Bring together various features too small to be a full module; may be
 * integrated in the next release of Omeka S, or not.
 *
 * @copyright Daniel Berthereau, 2018-2020
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        // Manage prev/next.
        $controllers = [
            'Omeka\Controller\Admin\Item',
            // 'Omeka\Controller\Admin\ItemSet',
            // 'Omeka\Controller\Admin\Media',
            'Omeka\Controller\Site\Item',
            // 'Omeka\Controller\Site\ItemSet',
            // 'Omeka\Controller\Site\Media',
        ];
        foreach ($controllers as $controller) {
            $sharedEventManager->attach(
                $controller,
                'view.browse.before',
                [$this, 'handleViewBrowse']
            );
        }
        $sharedEventManager->attach(
            \Search\Controller\IndexController::class,
            'view.layout',
            [$this, 'handleViewBrowse']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.show.sidebar',
            [$this, 'handleViewShowSidebarMedia']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.details',
            [$this, 'handleViewShowSidebarMedia']
        );

        // Main settings.
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_input_filters',
            [$this, 'handleMainSettingsFilters']
        );
        // Site settings.
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_input_filters',
            [$this, 'handleSiteSettingsFilters']
        );
    }

    public function handleViewShowSidebarMedia(Event $event): void
    {
        $view = $event->getTarget();
        $resource = $view->resource;
        echo $view->partial('admin/media/show-details-renderer', ['media' => $resource]);
    }

    public function handleViewBrowse(Event $event): void
    {
        $session = new Container('Next');
        if (!isset($session->lastBrowsePage)) {
            $session->lastBrowsePage = [];
            $session->lastQuery = [];
        }
        $params = $event->getTarget()->params();
        $ui = $params->fromRoute('__ADMIN__') ? 'admin' : 'public';
        // Why not use $this->getServiceLocator()->get('Request')->getServer()->get('REQUEST_URI')?
        $session->lastBrowsePage[$ui] = $_SERVER['REQUEST_URI'];
        // Remove any csrf key.
        $query = $params->fromQuery();
        foreach (array_keys($query) as $key) {
            if (substr($key, -4) === 'csrf') {
                unset($query[$key]);
            }
        }
        $session->lastQuery[$ui] = $query;
    }

    public function handleMainSettingsFilters(Event $event): void
    {
        $inputFilter = $event->getParam('inputFilter');
        $inputFilter
            ->get('next')
            ->add([
                'name' => 'next_property_itemset',
                'required' => false,
            ])
        ;

        /** @var \Omeka\Form\SettingForm $form */
        $form = $event->getTarget();
        $hasColumnsBrowse = $form->get('general')->has('columns_browse');
        if ($hasColumnsBrowse) {
            $form->get('next')->remove('next_columns_browse');
        } else {
            $columnsBrowse = $form->get('next')->get('next_columns_browse')->getValueOptions();
            $inputFilter
                ->get('next')
                ->add([
                    'name' => 'next_columns_browse',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'callback',
                            'options' => [
                                // The columns names are saved to simplify the creation
                                // of the browse view. Order is kept.
                                // FIXME Zend requires the values to be values, not keys, so there may be issues when labels are the same in different vocabularies.
                                'callback' => function ($columns) use ($columnsBrowse) {
                                    $result = [];
                                    foreach ($columns as $column) {
                                        if (isset($columnsBrowse[$column])) {
                                            $result[$columnsBrowse[$column]] = $column;
                                        } else {
                                            foreach ($columnsBrowse as $columnBrowse) {
                                                if (is_array($columnBrowse)) {
                                                    foreach ($columnBrowse['options'] as $property) {
                                                        if ($column === $property['value']) {
                                                            $result[$property['label']] = $column;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    return $result;
                                },
                            ],
                        ],
                    ],
                ]);
        }
    }

    public function handleSiteSettings(Event $event): void
    {
        parent::handleSiteSettings($event);

        $services = $this->getServiceLocator();

        $settings = $services->get('Omeka\Settings\Site');
        $orders = $settings->get('next_items_order_for_itemsets') ?: [];
        $ordersString = '';
        foreach ($orders as $ids => $order) {
            $ordersString .= $ids . ' ' . $order['sort_by'];
            if (isset($order['sort_order'])) {
                $ordersString .= ' ' . $order['sort_order'];
            }
            $ordersString .= "\n";
        }

        $settings = $services->get('Omeka\Settings\Site');
        $prepends = $settings->get('next_breadcrumbs_prepend') ?: [];
        $prependsString = '';
        foreach ($prepends as $prepend) {
            $prependsString .= $prepend['uri'] . ' ' . $prepend['label'] . "\n";
        }

        /**
         * @var \Omeka\Form\Element\RestoreTextarea $siteGroupsElement
         * @var \Internationalisation\Form\SettingsFieldset $fieldset
         */
        $fieldset = $event->getTarget()
            ->get('next');
        $fieldset
            ->get('next_items_order_for_itemsets')
            ->setValue($ordersString);
        $fieldset
            ->get('next_breadcrumbs_prepend')
            ->setValue($prependsString);
    }

    public function handleSiteSettingsFilters(Event $event): void
    {
        $event->getParam('inputFilter')
            ->get('next')
            ->add([
                'name' => 'next_items_order_for_itemsets',
                'required' => false,
                'filters' => [
                    [
                        'name' => \Laminas\Filter\Callback::class,
                        'options' => [
                            'callback' => [$this, 'filterResourceOrder'],
                        ],
                    ],
                ],
            ])
            ->add([
                'name' => 'next_breadcrumbs_crumbs',
                'required' => false,
            ])
            ->add([
                'name' => 'next_breadcrumbs_prepend',
                'required' => false,
                'filters' => [
                    [
                        'name' => \Laminas\Filter\Callback::class,
                        'options' => [
                            'callback' => [$this, 'filterBreadcrumbsPrepend'],
                        ],
                    ],
                ],
            ])
        ;
    }

    public function filterResourceOrder($string)
    {
        $list = $this->stringToList($string);

        // The setting is ordered by item set id for quicker check.
        // "0" is the default order, so it is always single.
        $result = [];
        foreach ($list as $row) {
            list($ids, $sortBy, $sortOrder) = array_filter(array_map('trim', explode(' ', $row, 3)));
            $ids = trim($ids, ', ');
            if (!strlen($ids) || empty($sortBy)) {
                continue;
            }
            $ids = explode(',', $ids);
            sort($ids);
            $ids = in_array('0', $ids)
                ? 0
                : implode(',', $ids);
            $result[$ids] = [
                'sort_by' => $sortBy,
                'sort_order' => strtolower($sortOrder) === 'desc' ? 'desc' : 'asc',
            ];
        }
        ksort($result);

        return $result;
    }

    public function filterBreadcrumbsPrepend($string)
    {
        return array_filter(array_map(function ($v) {
            list($uri, $label) = array_map('trim', explode(' ', $v, 2));
            if (!strlen($label)) {
                $label = $uri;
            }
            return ['label' => $label, 'uri' => $uri];
        }, $this->stringToList($string)));
    }
}
