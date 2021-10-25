<?php declare(strict_types=1);

namespace Next\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    protected $label = 'Next module'; // @translate

    public function init(): void
    {
        $this
            ->add([
                'name' => 'next_items_order_for_itemsets',
                'type' => Element\Textarea::class,
                'options' => [
                    'label' => 'Default items order in each item set', // @translate
                    'info' => 'Set order for item set, one by row, format "id,id,id property order". Use "0" for the default.', // @translate
                ],
                'attributes' => [
                    'id' => 'next_items_order_for_itemsets',
                    'placeholder' => '0 dcterms:identifier asc
17,24 created desc
73 dcterms:title asc',
                ],
            ])
            ->add([
                'name' => 'next_prevnext_items_query',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Query to limit and sort the list of items for the previous/next buttons', // @translate
                    'info' => 'Use a standard query. Arguments from module Advanced Search Plus are supported if present and needed.', // @translate
                    'documentation' => 'https://omeka.org/s/docs/user-manual/sites/site_pages/#browse-preview',
                ],
                'attributes' => [
                    'id' => 'next_prevnext_items_query',
                ],
            ])
            ->add([
                'name' => 'next_prevnext_item_sets_query',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Query to limit and sort the list of item sets for the previous/next buttons', // @translate
                    'info' => 'Use a standard query. Arguments from module Advanced Search Plus are supported if present and needed.', // @translate
                    'documentation' => 'https://omeka.org/s/docs/user-manual/sites/site_pages/#browse-preview',
                ],
                'attributes' => [
                    'id' => 'next_prevnext_item_sets_query',
                ],
            ]);
    }
}
