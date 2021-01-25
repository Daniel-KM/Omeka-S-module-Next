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
                        'collections' => 'Include "Collections"', // @translate,
                        'itemset' => 'Include main item set for item', // @translate,
                        'current' => 'Append current resource', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'next_breadcrumbs_crumbs',
                ],
            ])
            // TODO Convert textarea into array before saving and vice-versa (see ConfigForm).
            ->add([
                'name' => 'next_breadcrumbs_prepend',
                'type' => Element\Textarea::class,
                'options' => [
                    'label' => 'Prepended links', // @translate
                    'info' => 'List of urls followed by a label, one by line, that will be prepended to the breadcrumb.', // @translate
                ],
                'attributes' => [
                    'id' => 'next_breadcrumbs_prepend',
                    'placeholder' => '/s/my-site/page/intermediate Example page',
                ],
            ])
            ->add([
                'name' => 'next_breadcrumbs_collections_url',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Url for collections', // @translate
                    'info' => 'The url to use for the link "Collections", if set above. Let empty to use the default one.', // @translate
                ],
                'attributes' => [
                    'id' => 'next_breadcrumbs_collections_url',
                    'placeholder' => '/s/my-site/search?resource-type=item_sets',
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
            ])
            ->add([
                'name' => 'next_breadcrumbs_homepage',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Display on home page', // @translate
                ],
                'attributes' => [
                    'id' => 'next_breadcrumbs_homepage',
                ],
            ]);
    }
}
