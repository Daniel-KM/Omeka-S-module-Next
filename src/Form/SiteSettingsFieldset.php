<?php
namespace Next\Form;

use Omeka\Form\Element\PropertySelect;
use Zend\Form\Element;
use Zend\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    protected $label = 'Next Module'; // @translate

    public function init()
    {
        $this
            ->add([
                'type' => Element\Checkbox::class,
                'name' => 'next_search_used_terms',
                'options' => [
                    'label' => 'List only used properties and resources classes', // @translate
                    'info' => 'Restrict the list of properties and resources classes to the used ones in advanced search form (for properties, when option "templates" is not used).', // @translate
                ],
                'attributes' => [
                    'id' => 'next_search_used_terms',
                ],
            ])
            ->add([
                'name' => 'next_breadcrumbs_property_itemset',
                'type' => PropertySelect::class,
                'options' => [
                    'label' => 'Property for parent item set for breadcrumbs', // @translate
                    'info' => 'When an item is included in multiple item sets, the one that will be displayed will be the first item set in this property. If empty, the item set crumb will be skipped in that case.', // @translate
                    'empty_option' => '',
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'id' => 'next_breadcrumbs_property_itemset',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select a property…', // @translate
                ],
            ]);
    }
}
