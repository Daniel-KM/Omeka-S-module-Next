<?php
namespace Next;

use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Form\Element;

/**
 * Next
 *
 * Bring together various features too small to be a full module; may be
 * integrated in the next release of Omeka S, or not.
 *
 * @copyright Daniel Berthereau, 2018
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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

            $sharedEventManager->attach(
                $adapter,
                'api.create.pre',
                [$this, 'handleResourceProcessPre']
            );

            $sharedEventManager->attach(
                $adapter,
                'api.update.pre',
                [$this, 'handleResourceProcessPre']
            );

            $sharedEventManager->attach(
                $adapter,
                'api.batch_update.post',
                [$this, 'handleResourceBatchUpdatePost']
            );
        }

        $sharedEventManager->attach(
            \Omeka\Form\ResourceBatchUpdateForm::class,
            'form.add_elements',
            [$this, 'formAddElementsResourceBatchUpdateForm']
        );
    }

    public function apiSearchQuery(Event $event)
    {
        // Add the random sort.
        $query = $event->getParam('request')->getContent();
        if (isset($query['sort_by']) && $query['sort_by'] === 'random') {
            $qb = $event->getParam('queryBuilder');
            $qb->orderBy('RAND()');
        }
    }

    /**
     * Process action on create/update.
     *
     * - preventive trim on property values.
     * - preventive deduplication on property values
     *
     * @param Event $event
     */
    public function handleResourceProcessPre(Event $event)
    {
        /** @var \Omeka\Api\Request $request */
        $request = $event->getParam('request');
        $data = $request->getContent();

        $trimUnicode = function ($v) {
            return preg_replace('/^[\h\v\s[:blank:][:space:]]+|[\h\v\s[:blank:][:space:]]+$/u', '', $v);
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
     */
    public function handleResourceBatchUpdatePost(Event $event)
    {
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

    public function formAddElementsResourceBatchUpdateForm(Event $event)
    {
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
}
