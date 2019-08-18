<?php
namespace Next\Form;

use Omeka\Form\Element\PropertySelect;
use Zend\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Next Module'; // @translate

    public function init()
    {
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
                    'id' => 'next_breadcrumbs_property_itemset',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select a property…', // @translate
                ],
            ]);
    }
}
