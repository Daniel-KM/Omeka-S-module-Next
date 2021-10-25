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
    public function setSettings(Settings $settings): SettingsFieldset
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
