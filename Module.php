<?php
namespace Next;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Doctrine\ORM\QueryBuilder;
use Generic\AbstractModule;
use Omeka\Api\Adapter\AbstractResourceEntityAdapter;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Form\Element;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Session\Container;

/**
 * Next
 *
 * Bring together various features too small to be a full module; may be
 * integrated in the next release of Omeka S, or not.
 *
 * @copyright Daniel Berthereau, 2018-2019
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()
            ->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onMergeConfig']);
    }

    public function onMergeConfig(ModuleEvent $event)
    {
        // When module BulkEdit is installed and enabled, its controller plugins
        // should be used, not the Next ones.
        $configListener = $event->getConfigListener();
        $config = $configListener->getMergedConfig(false);
        if (!isset($config['bulkedit'])) {
            return;
        }

        $config['controller_plugins']['factories']['trimValues'] = \BulkEdit\Service\ControllerPlugin\TrimValuesFactory::class;
        $config['controller_plugins']['factories']['deduplicateValues'] = \BulkEdit\Service\ControllerPlugin\DeduplicateValuesFactory::class;
        $configListener->setMergedConfig($config);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $adapters = [
            \Omeka\Api\Adapter\ItemAdapter::class,
            \Omeka\Api\Adapter\ItemSetAdapter::class,
            \Omeka\Api\Adapter\MediaAdapter::class,
        ];
        foreach ($adapters as $adapter) {
            $sharedEventManager->attach(
                $adapter,
                'api.search.query',
                [$this, 'apiSearchQuery']
            );

            // Deprecated Use module BulkEdit.
            $sharedEventManager->attach(
                $adapter,
                'api.create.pre',
                [$this, 'handleResourceProcessPre']
            );

            // Deprecated Use module BulkEdit.
            $sharedEventManager->attach(
                $adapter,
                'api.update.pre',
                [$this, 'handleResourceProcessPre']
            );

            // Deprecated Use module BulkEdit.
            $sharedEventManager->attach(
                $adapter,
                'api.batch_update.post',
                [$this, 'handleResourceBatchUpdatePost']
            );
        }

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
            \Omeka\Api\Adapter\PropertyAdapter::class,
            'api.search.query',
            [$this, 'apiSearchQueryProperty']
        );
        $sharedEventManager->attach(
            \Omeka\Api\Adapter\ResourceClassAdapter::class,
            'api.search.query',
            [$this, 'apiSearchQueryResourceClass']
        );
        $sharedEventManager->attach(
            \Omeka\Api\Adapter\SitePageAdapter::class,
            'api.search.query',
            [$this, 'apiSearchQuerySitePage']
        );

        $sharedEventManager->attach(
            \Omeka\Form\Element\PropertySelect::class,
            'form.vocab_member_select.query',
            [$this, 'formVocabMemberSelectQuery']
        );
        $sharedEventManager->attach(
            \Omeka\Form\Element\ResourceClassSelect::class,
            'form.vocab_member_select.query',
            [$this, 'formVocabMemberSelectQuery']
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

        // Deprecated Use module BulkEdit.
        $sharedEventManager->attach(
            \Omeka\Form\ResourceBatchUpdateForm::class,
            'form.add_elements',
            [$this, 'formAddElementsResourceBatchUpdateForm']
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

    public function apiSearchQuery(Event $event)
    {
        $adapter = $event->getTarget();
        $qb = $event->getParam('queryBuilder');
        $query = $event->getParam('request')->getContent();

        // Add the random sort.
        if (isset($query['sort_by']) && $query['sort_by'] === 'random') {
            $qb->orderBy('RAND()');
        }

        // Advanced property search.
        $this->buildPropertyQuery($qb, $query, $adapter);
    }

    public function apiSearchQueryProperty(Event $event)
    {
        $query = $event->getParam('request')->getContent();

        // This key is used by PropertySelect.
        if (!empty($query['used'])) {
            $isOldOmeka = \Omeka\Module::VERSION < 2;
            $alias = $isOldOmeka ? \Omeka\Entity\Property::class : 'omeka_root';

            $adapter = $event->getTarget();
            $qb = $event->getParam('queryBuilder');
            $expr = $qb->expr();

            $valuesAlias = $adapter->createAlias();
            $qb->innerJoin(
                $alias . '.values',
                $valuesAlias,
                \Doctrine\ORM\Query\Expr\Join::WITH,
                $expr->eq($valuesAlias . '.property', $alias . '.id')
            );
        }
    }

    public function apiSearchQueryResourceClass(Event $event)
    {
        $query = $event->getParam('request')->getContent();

        // This key is used by ResourceClassSelect.
        if (!empty($query['used'])) {
            $isOldOmeka = \Omeka\Module::VERSION < 2;
            $alias = $isOldOmeka ? \Omeka\Entity\ResourceClass::class : 'omeka_root';

            $adapter = $event->getTarget();
            $qb = $event->getParam('queryBuilder');
            $expr = $qb->expr();

            $resourceAlias = $adapter->createAlias();
            $qb->innerJoin(
                $alias . '.resources',
                $resourceAlias,
                \Doctrine\ORM\Query\Expr\Join::WITH,
                $expr->eq($resourceAlias . '.resourceClass', $alias . '.id')
            );
        }
    }

    public function apiSearchQuerySitePage(Event $event)
    {
        $isOldOmeka = \Omeka\Module::VERSION < 2;
        $alias = $isOldOmeka ? \Omeka\Entity\SitePage::class : 'omeka_root';

        $adapter = $event->getTarget();
        $qb = $event->getParam('queryBuilder');
        $expr = $qb->expr();
        $query = $event->getParam('request')->getContent();

        if (isset($query['slug'])) {
            $qb->andWhere($expr->eq(
                $alias . '.slug',
                $adapter->createNamedParameter($qb, $query['slug'])
            ));
        }

        if (isset($query['site_id'])) {
            $qb->andWhere($expr->eq($alias . '.site', $query['site_id']));
        }

        if (isset($query['site_slug'])) {
            $siteAlias = $adapter->createAlias();
            $qb->innerJoin(
                $alias . '.site',
                $siteAlias
            );
            $qb->andWhere($expr->eq(
                "$siteAlias.slug",
                $adapter->createNamedParameter($qb, $query['site_slug'])
            ));
        }
    }

    public function formVocabMemberSelectQuery(Event $event)
    {
        $selectElement = $event->getTarget();
        if ($selectElement->getOption('used_terms')) {
            $query = $event->getParam('query', []);
            $query['used'] = true;
            $event->setParam('query', $query);
        }
    }

    /**
     * Process action on create/update.
     *
     * - preventive trim on property values.
     * - preventive deduplication on property values
     *
     * @param Event $event
     * @deprecated Use module BulkEdit.
     */
    public function handleResourceProcessPre(Event $event)
    {
        if ($this->isModuleActive('BulkEdit')) {
            return;
        }

        /** @var \Omeka\Api\Request $request */
        $request = $event->getParam('request');
        $data = $request->getContent();

        $trimUnicode = function ($v) {
            return preg_replace('/^[\s\h\v[:blank:][:space:]]+|[\s\h\v[:blank:][:space:]]+$/u', '', $v);
        };

        // Trimming.
        foreach ($data as $term => &$values) {
            // Process properties only.
            if (strpos($term, ':') === false || !is_array($values) || empty($values)) {
                continue;
            }
            $first = reset($values);
            if (empty($first['property_id'])) {
                continue;
            }
            foreach ($values as &$value) {
                if (isset($value['@value'])) {
                    $v = $trimUnicode($value['@value']);
                    $value['@value'] = strlen($v) ? $v : null;
                }
                if (isset($value['@id'])) {
                    $v = $trimUnicode($value['@id']);
                    $value['@id'] = strlen($v) ? $v : null;
                }
                if (isset($value['@language'])) {
                    $v = $trimUnicode($value['@language']);
                    $value['@language'] = strlen($v) ? $v : null;
                }
                if (isset($value['o:label'])) {
                    $v = $trimUnicode($value['o:label']);
                    $value['o:label'] = strlen($v) ? $v : null;
                }
            }
            unset($value);
        }
        unset($values);

        // Deduplicating.
        foreach ($data as $term => &$values) {
            // Process properties only.
            if (strpos($term, ':') === false || !is_array($values) || empty($values)) {
                continue;
            }
            $first = reset($values);
            if (empty($first['property_id'])) {
                continue;
            }
            // Reorder all keys of all the values to simplify strict check.
            foreach ($values as &$value) {
                ksort($value);
            }
            unset($value);
            $test = [];
            foreach ($values as $key => $value) {
                if (in_array($value, $test, true)) {
                    unset($values[$key]);
                } else {
                    $test[$key] = $value;
                }
            }
        }
        unset($values);

        $request->setContent($data);
    }

    /**
     * Process action on batch update (all or partial).
     *
     * - curative trim on property values.
     *
     * Data may need to be reindexed if a module like Search is used, even if
     * the results are probably the same with a simple trimming.
     *
     * @param Event $event
     * @deprecated Use module BulkEdit.
     */
    public function handleResourceBatchUpdatePost(Event $event)
    {
        if ($this->isModuleActive('BulkEdit')) {
            return;
        }

        /** @var \Omeka\Api\Request $request */
        $request = $event->getParam('request');
        $trimValues = $request->getValue('trim_values');
        $deduplicateValues = $request->getValue('deduplicate_values');
        if (!$trimValues && !$deduplicateValues) {
            return;
        }

        $services = $this->getServiceLocator();
        $plugins = $services->get('ControllerPluginManager');

        if ($trimValues) {
            /** @var \Next\Mvc\Controller\Plugin\TrimValues $trimValues */
            $trimValues = $plugins->get('trimValues');
            $ids = (array) $request->getIds();
            $trimValues($ids);
        }

        if ($deduplicateValues) {
            /** @var \Next\Mvc\Controller\Plugin\DeduplicateValues $deduplicateValues */
            $deduplicateValues = $plugins->get('deduplicateValues');
            $ids = (array) $request->getIds();
            $deduplicateValues($ids);
        }
    }

    public function handleViewShowSidebarMedia(Event $event)
    {
        $view = $event->getTarget();
        $resource = $view->resource;
        echo $view->partial('admin/media/show-details-renderer', ['media' => $resource]);
    }

    /**
     * @param Event $event
     * @deprecated Use module BulkEdit.
     */
    public function formAddElementsResourceBatchUpdateForm(Event $event)
    {
        if ($this->isModuleActive('BulkEdit')) {
            return;
        }

        $form = $event->getTarget();

        $form->add([
            'name' => 'trim_values',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Trim property values', // @translate
                'info' => 'Remove initial and trailing whitespace of all values of all properties', // @translate
            ],
            'attributes' => [
                'id' => 'trim_values',
                // This attribute is required to make "batch edit all" working.
                'data-collection-action' => 'replace',
            ],
        ]);

        $form->add([
            'name' => 'deduplicate_values',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Deduplicate property values case insensitively', // @translate
                'info' => 'Deduplicate values of all properties, case INsensitively. Trimming values before is recommended, because values are checked strictly.', // @translate
            ],
            'attributes' => [
                'id' => 'deduplicate_values',
                // This attribute is required to make "batch edit all" working.
                'data-collection-action' => 'replace',
            ],
        ]);
    }

    public function handleViewBrowse(Event $event)
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
        $session->lastQuery[$ui] = $params->fromQuery();
    }

    public function handleMainSettingsFilters(Event $event)
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
            ;
        }
    }

    public function handleSiteSettings(Event $event)
    {
        parent::handleSiteSettings($event);

        $services = $this->getServiceLocator();

        $space = strtolower(__NAMESPACE__);

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

        /**
         * @var \Omeka\Form\Element\RestoreTextarea $siteGroupsElement
         * @var \Internationalisation\Form\SettingsFieldset $fieldset
         */
        $fieldset = $event->getTarget()
            ->get($space);
        $fieldset
            ->get('next_items_order_for_itemsets')
            ->setValue($ordersString);
    }

    public function handleSiteSettingsFilters(Event $event)
    {
        $event->getParam('inputFilter')
            ->get('next')
            ->add([
                'name' => 'next_items_order_for_itemsets',
                'required' => false,
                'filters' => [
                    [
                        'name' => \Zend\Filter\Callback::class,
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

    /**
     * Build query on value.
     *
     * Complete \Omeka\Api\Adapter\AbstractResourceEntityAdapter::buildPropertyQuery()
     *
     * Note: because this filter is separate from the core one, all the
     * properties are rechecked to avoid a issue with the joiner (or/and).
     * @todo Find a way to not recheck all arguments used to search properties as long as it's not in the core.
     *
     * Query format:
     *
     * - property[{index}][joiner]: "and" OR "or" joiner with previous query
     * - property[{index}][property]: property ID
     * - property[{index}][text]: search text
     * - property[{index}][type]: search type
     *   - eq: is exactly
     *   - neq: is not exactly
     *   - in: contains
     *   - nin: does not contain
     *   - ex: has any value
     *   - nex: has no value
     *   - list: is in list
     *   - nlist: is not in list
     *   - sw: starts with
     *   - nsw: does not start with
     *   - ew: ends with
     *   - new: does not end with
     *   - res: has resource
     *   - nres: has no resource
     *
     * @param QueryBuilder $qb
     * @param array $query
     * @param AbstractResourceEntityAdapter $adapter
     */
    protected function buildPropertyQuery(QueryBuilder $qb, array $query, AbstractResourceEntityAdapter $adapter)
    {
        if (!isset($query['property']) || !is_array($query['property'])) {
            return;
        }

        $isOldOmeka = \Omeka\Module::VERSION < 2;
        if ($isOldOmeka) {
            return $this->buildPropertyQueryOld($qb, $query, $adapter);
        }

        $valuesJoin = 'omeka_root.values';
        $where = '';
        $expr = $qb->expr();

        $escape = function ($string) {
            return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $string);
        };

        foreach ($query['property'] as $queryRow) {
            if (!(
                is_array($queryRow)
                && array_key_exists('property', $queryRow)
                && array_key_exists('type', $queryRow)
            )) {
                continue;
            }
            $propertyId = $queryRow['property'];
            $queryType = $queryRow['type'];
            $joiner = isset($queryRow['joiner']) ? $queryRow['joiner'] : null;
            $value = isset($queryRow['text']) ? $queryRow['text'] : null;

            if (!strlen($value) && $queryType !== 'nex' && $queryType !== 'ex') {
                continue;
            }

            $valuesAlias = $adapter->createAlias();
            $positive = true;

            switch ($queryType) {
                case 'neq':
                    $positive = false;
                    // no break.
                case 'eq':
                    $param = $adapter->createNamedParameter($qb, $value);
                    $subqueryAlias = $adapter->createAlias();
                    $subquery = $adapter->getEntityManager()
                        ->createQueryBuilder()
                        ->select("$subqueryAlias.id")
                        ->from('Omeka\Entity\Resource', $subqueryAlias)
                        ->where($expr->eq("$subqueryAlias.title", $param));
                    $predicateExpr = $expr->orX(
                        $expr->in("$valuesAlias.valueResource", $subquery->getDQL()),
                        $expr->eq("$valuesAlias.value", $param),
                        $expr->eq("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nin':
                    $positive = false;
                    // no break.
                case 'in':
                    $param = $adapter->createNamedParameter($qb, '%' . $escape($value) . '%');
                    $subqueryAlias = $adapter->createAlias();
                    $subquery = $adapter->getEntityManager()
                        ->createQueryBuilder()
                        ->select("$subqueryAlias.id")
                        ->from('Omeka\Entity\Resource', $subqueryAlias)
                        ->where($expr->like("$subqueryAlias.title", $param));
                    $predicateExpr = $expr->orX(
                        $expr->in("$valuesAlias.valueResource", $subquery->getDQL()),
                        $expr->like("$valuesAlias.value", $param),
                        $expr->like("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nlist':
                    $positive = false;
                    // no break.
                case 'list':
                    $list = is_array($value) ? $value : explode("\n", $value);
                    $list = array_filter(array_map('trim', $list), 'strlen');
                    if (empty($list)) {
                        continue 2;
                    }
                    $param = $adapter->createNamedParameter($qb, $list);
                    $subqueryAlias = $adapter->createAlias();
                    $subquery = $adapter->getEntityManager()
                        ->createQueryBuilder()
                        ->select("$subqueryAlias.id")
                        ->from('Omeka\Entity\Resource', $subqueryAlias)
                        ->where($expr->eq("$subqueryAlias.title", $param));
                    $predicateExpr = $expr->orX(
                        $expr->in("$valuesAlias.valueResource", $subquery->getDQL()),
                        $expr->in("$valuesAlias.value", $param),
                        $expr->in("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nsw':
                    $positive = false;
                    // no break.
                case 'sw':
                    $param = $adapter->createNamedParameter($qb, $escape($value) . '%');
                    $subqueryAlias = $adapter->createAlias();
                    $subquery = $adapter->getEntityManager()
                        ->createQueryBuilder()
                        ->select("$subqueryAlias.id")
                        ->from('Omeka\Entity\Resource', $subqueryAlias)
                        ->where($expr->like("$subqueryAlias.title", $param));
                    $predicateExpr = $expr->orX(
                        $expr->in("$valuesAlias.valueResource", $subquery->getDQL()),
                        $expr->like("$valuesAlias.value", $param),
                        $expr->like("$valuesAlias.uri", $param)
                    );
                    break;

                case 'new':
                    $positive = false;
                    // no break.
                case 'ew':
                    $param = $adapter->createNamedParameter($qb, '%' . $escape($value));
                    $subqueryAlias = $adapter->createAlias();
                    $subquery = $adapter->getEntityManager()
                        ->createQueryBuilder()
                        ->select("$subqueryAlias.id")
                        ->from('Omeka\Entity\Resource', $subqueryAlias)
                        ->where($expr->like("$subqueryAlias.title", $param));
                    $predicateExpr = $expr->orX(
                        $expr->in("$valuesAlias.valueResource", $subquery->getDQL()),
                        $expr->like("$valuesAlias.value", $param),
                        $expr->like("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nres':
                    $positive = false;
                    // no break.
                case 'res':
                    $predicateExpr = $expr->eq(
                        "$valuesAlias.valueResource",
                        $adapter->createNamedParameter($qb, $value)
                    );
                    break;

                case 'nex':
                    $positive = false;
                    // no break.
                case 'ex':
                    $predicateExpr = $expr->isNotNull("$valuesAlias.id");
                    break;

                default:
                    continue 2;
            }

            $joinConditions = [];
            // Narrow to specific property, if one is selected
            if ($propertyId) {
                if (is_numeric($propertyId)) {
                    $propertyId = (int) $propertyId;
                } else {
                    $property = $adapter->getPropertyByTerm($propertyId);
                    if ($property) {
                        $propertyId = $property->getId();
                    } else {
                        $propertyId = 0;
                    }
                }
                $joinConditions[] = $expr->eq("$valuesAlias.property", (int) $propertyId);
            }

            if ($positive) {
                $whereClause = '(' . $predicateExpr . ')';
            } else {
                $joinConditions[] = $predicateExpr;
                $whereClause = $expr->isNull("$valuesAlias.id");
            }

            if ($joinConditions) {
                $qb->leftJoin($valuesJoin, $valuesAlias, 'WITH', $expr->andX(...$joinConditions));
            } else {
                $qb->leftJoin($valuesJoin, $valuesAlias);
            }

            if ($where == '') {
                $where = $whereClause;
            } elseif ($joiner == 'or') {
                $where .= " OR $whereClause";
            } else {
                $where .= " AND $whereClause";
            }
        }

        if ($where) {
            $qb->andWhere($where);
        }
    }

    /**
     * Old version to build a property query.
     *
     * @param QueryBuilder $qb
     * @param array $query
     * @param AbstractResourceEntityAdapter $adapter
     */
    protected function buildPropertyQueryOld(QueryBuilder $qb, array $query, AbstractResourceEntityAdapter $adapter)
    {
        if (!isset($query['property']) || !is_array($query['property'])) {
            return;
        }
        $valuesJoin = $adapter->getEntityClass() . '.values';
        $where = '';
        $expr = $qb->expr();

        $escape = function ($string) {
            return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $string);
        };

        foreach ($query['property'] as $queryRow) {
            if (!(
                is_array($queryRow)
                && array_key_exists('property', $queryRow)
                && array_key_exists('type', $queryRow)
            )) {
                continue;
            }
            $propertyId = $queryRow['property'];
            $queryType = $queryRow['type'];
            $joiner = isset($queryRow['joiner']) ? $queryRow['joiner'] : null;
            $value = isset($queryRow['text']) ? $queryRow['text'] : null;

            if (!strlen($value) && $queryType !== 'nex' && $queryType !== 'ex') {
                continue;
            }

            $valuesAlias = $adapter->createAlias();
            $positive = true;

            switch ($queryType) {
                case 'neq':
                    $positive = false;
                    // no break.
                case 'eq':
                    $param = $adapter->createNamedParameter($qb, $value);
                    $predicateExpr = $expr->orX(
                        $expr->eq("$valuesAlias.value", $param),
                        $expr->eq("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nin':
                    $positive = false;
                    // no break.
                case 'in':
                    $param = $adapter->createNamedParameter($qb, '%' . $escape($value) . '%');
                    $predicateExpr = $expr->orX(
                        $expr->like("$valuesAlias.value", $param),
                        $expr->like("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nlist':
                    $positive = false;
                    // no break.
                case 'list':
                    $list = is_array($value) ? $value : explode("\n", $value);
                    $list = array_filter(array_map('trim', $list), 'strlen');
                    if (empty($list)) {
                        continue 2;
                    }
                    $param = $adapter->createNamedParameter($qb, $list);
                    $predicateExpr = $expr->orX(
                        $expr->in("$valuesAlias.value", $param),
                        $expr->in("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nsw':
                    $positive = false;
                    // no break.
                case 'sw':
                    $param = $adapter->createNamedParameter($qb, $escape($value) . '%');
                    $predicateExpr = $expr->orX(
                        $expr->like("$valuesAlias.value", $param),
                        $expr->like("$valuesAlias.uri", $param)
                    );
                    break;

                case 'new':
                    $positive = false;
                    // no break.
                case 'ew':
                    $param = $adapter->createNamedParameter($qb, '%' . $escape($value));
                    $predicateExpr = $expr->orX(
                        $expr->like("$valuesAlias.value", $param),
                        $expr->like("$valuesAlias.uri", $param)
                    );
                    break;

                case 'nres':
                    $positive = false;
                    // no break.
                case 'res':
                    $predicateExpr = $expr->eq(
                        "$valuesAlias.valueResource",
                        $adapter->createNamedParameter($qb, $value)
                    );
                    break;

                case 'nex':
                    $positive = false;
                    // no break.
                case 'ex':
                    $predicateExpr = $expr->isNotNull("$valuesAlias.id");
                    break;

                default:
                    continue 2;
            }

            $joinConditions = [];
            // Narrow to specific property, if one is selected
            if ($propertyId) {
                if (is_numeric($propertyId)) {
                    $propertyId = (int) $propertyId;
                } else {
                    $property = $adapter->getPropertyByTerm($propertyId);
                    if ($property) {
                        $propertyId = $property->getId();
                    } else {
                        $propertyId = 0;
                    }
                }
                $joinConditions[] = $expr->eq("$valuesAlias.property", (int) $propertyId);
            }

            if ($positive) {
                $whereClause = '(' . $predicateExpr . ')';
            } else {
                $joinConditions[] = $predicateExpr;
                $whereClause = $expr->isNull("$valuesAlias.id");
            }

            if ($joinConditions) {
                $qb->leftJoin($valuesJoin, $valuesAlias, 'WITH', $expr->andX(...$joinConditions));
            } else {
                $qb->leftJoin($valuesJoin, $valuesAlias);
            }

            if ($where == '') {
                $where = $whereClause;
            } elseif ($joiner == 'or') {
                $where .= " OR $whereClause";
            } else {
                $where .= " AND $whereClause";
            }
        }

        if ($where) {
            $qb->andWhere($where);
        }
    }
}
