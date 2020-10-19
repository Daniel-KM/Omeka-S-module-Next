<?php declare(strict_types=1);
namespace Next\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Omeka\Form\Element\PropertySelect;
use Omeka\Settings\Settings;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Next Module'; // @translate

    /**
     * @var Settings
     */
    protected $settings;

    public function init(): void
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
                    'info' => 'When an item is included in multiple item sets, the main one may be determined by this property.', // @translate
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
                'name' => 'next_columns_browse',
                'type' => PropertySelect::class,
                'options' => [
                    'label' => 'Columns for browse views', // @translate
                    'info' => 'These columns will be used in the admin resource browse views.', // @translate
                    'term_as_value' => true,
                    'prepend_value_options' => $prependedValues,
                ],
                'attributes' => [
                    'id' => 'next-columns-browse',
                    // TODO Keep the original order of the columns via js.
                    'value' => array_values($this->getSettings()->get('next_columns_browse', [])) ?: $defaultColumnsBrowse,
                    'required' => false,
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select columns…', // @translate
                ],
            ])
            ->add([
                'name' => 'next_prevnext_disable',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Disable previous/next', // @translate
                    'info' => 'An issue exists on some versions of mysql database (mariadb is working fine).', // @translate
                ],
                'attributes' => [
                    'id' => 'next-prevnext-disable',
                ],
            ])
        ;
    }

    /**
     * @param Settings $settings
     */
    public function setSettings(Settings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
