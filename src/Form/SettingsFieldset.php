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
                'name' => 'breadcrumbs',
                'type' => Fieldset::class,
                'options' => [
                    'label' => 'Next Module : Breadcrumbs', // @translate
                ],
            ]);
        // Fieldset is only for display currently.
        // $fieldset = $this->get('breadcrumbs');
        $fieldset = $this;
        $fieldset
            ->add([
                'name' => 'next_breadcrumbs_property_itemset',
                'type' => PropertySelect::class,
                'options' => [
                    'label' => 'Property for parent item set of item', // @translate
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
