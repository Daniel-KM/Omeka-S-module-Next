<?php
namespace Next\Form;

use Omeka\Form\Element\PropertySelect;
use Zend\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Next Module'; // @translate

    public function init()
    {
        $defaultColumnsBrowse = [
            'resource_class_label',
            'owner_name',
            'created',
        ];
        $prependedValues = [
            'id' => 'Internal id', // @translate
            'resource_class_label' => 'Resource class', // @translate
            'resource_template_label' => 'Resource template', // @translate
            'owner_name' => 'Owner', // @translate
            'created' => 'Created', // @translate
            'modified' => 'Modified', // @translate
        ];

        $this
            ->add([
                'name' => 'next_property_itemset',
                'type' => PropertySelect::class,
                'options' => [
                    'label' => 'Property to set primary item set', // @translate
                    'info' => 'When an item is included in multiple item sets, the first one may be determined by this property.', // @translate
                    'empty_option' => '',
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'id' => 'next_property_itemset',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select a property…', // @translate
                ],
            ])
            // Check is done in filter.
            ->add([
                'name' => 'columns_browse',
                'type' => PropertySelect::class,
                'options' => [
                    'label' => 'Columns for browse views', // @translate
                    'info' => 'These columns will be used in the admin resource browse views.', // @translate
                    'term_as_value' => true,
                    'prepend_value_options' => $prependedValues,
                ],
                'attributes' => [
                    'id' => 'columns-browse',
                    // TODO Keep the original order of the columns via js.
                    'value' => array_values($this->settings->get('columns_browse', [])) ?: $defaultColumnsBrowse,
                    'required' => false,
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select columns…', // @translate
                ],
            ])
        ;
    }
}
