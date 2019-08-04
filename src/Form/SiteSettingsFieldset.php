<?php
namespace Next\Form;

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
            ]);

        $this
            ->add([
                'name' => 'breadcrumbs',
                'type' => Fieldset::class,
                'options' => [
                    'label' => 'Next module : Breadcrumbs', // @translate
                ],
            ]);
        // Fieldset is only for display currently.
        // $fieldset = $this->get('breadcrumbs');
        $fieldset = $this;
        $fieldset
            ->add([
                'name' => 'next_breadcrumbs_crumbs',
                'type' => Element\MultiCheckbox::class,
                'options' => [
                    'label' => 'Crumbs', // @translate
                    'value_options' => [
                        'home' => 'Prepend home', // @translate
                        'homepage' => 'Display on home page', // @translate
                        'current' => 'Append current resource', // @translate
                        'itemset' => 'Include item set for item', // @translate,
                    ],
                ],
                'attributes' => [
                    'id' => 'next_breadcrumbs_crumbs',
                ],
            ])
            ->add([
                'name' => 'next_breadcrumbs_separator',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Separator', // @translate
                    'info' => 'The separator between crumbs may be set as raw text or via css. it should be set as an html text ("&gt;").', // @translate
                ],
                'attributes' => [
                    'id' => 'next_breadcrumbs_separator',
                    'placeholder' => '&gt;',
                ],
            ]);
    }
}
