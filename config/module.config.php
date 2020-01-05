<?php
namespace Next;

$config = [
    'listeners' => [
        Mvc\MvcListeners::class,
    ],
    'service_manager' => [
        'invokables' => [
            Mvc\MvcListeners::class => Mvc\MvcListeners::class,
            'Omeka\ViewApiJsonRenderer' => View\Renderer\ApiJsonRenderer::class,
        ],
        'factories' => [
            'Omeka\File\TempFileFactory' => Service\File\TempFileFactoryFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'breadcrumbs' => View\Helper\Breadcrumbs::class,
            'currentSite' => View\Helper\CurrentSite::class,
            'isHomePage' => View\Helper\IsHomePage::class,
            'itemSetPosition' => View\Helper\ItemSetPosition::class,
            'lastBrowsePage' => View\Helper\LastBrowsePage::class,
            'primaryItemSet' => View\Helper\PrimaryItemSet::class,
            'searchFilters' => View\Helper\SearchFilters::class,
            'thumbnailUrl' => View\Helper\ThumbnailUrl::class,
            'userBar' => View\Helper\UserBar::class,
        ],
        'factories' => [
            'browsePreviousNext' => Service\ViewHelper\BrowsePreviousNextFactory::class,
            'defaultSiteSlug' => Service\ViewHelper\DefaultSiteSlugFactory::class,
            // 'logger' => Service\ViewHelper\LoggerFactory::class,
            'nextResource' => Service\ViewHelper\NextResourceFactory::class,
            'previousResource' => Service\ViewHelper\PreviousResourceFactory::class,
            'publicResourceUrl' => Service\ViewHelper\PublicResourceUrlFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
            Form\SearchFormFieldset::class => Form\SearchFormFieldset::class,
            Form\SimplePageFieldset::class => Form\SimplePageFieldset::class,
        ],
        'factories' => [
            Form\Element\SitesPageSelect::class => Service\Form\Element\SitesPageSelectFactory::class,
            Form\SettingsFieldset::class => Service\Form\SettingsFieldsetFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
            // Deprecated Use module BulkEdit.
            'trimValues' => Service\ControllerPlugin\TrimValuesFactory::class,
            'deduplicateValues' => Service\ControllerPlugin\DeduplicateValuesFactory::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'next' => [
        'settings' => [
            'next_property_itemset' => '',
            'next_columns_browse' => [
                'resource_class_label',
                'owner_name',
                'created',
            ],
        ],
        'site_settings' => [
            'next_items_order_for_itemsets' => [],
            'next_search_used_terms' => true,
            'next_breadcrumbs_crumbs' => [
                'home',
                // 'homepage',
                'current',
                'itemset',
            ],
            // TODO Convert this setting into an array of links for breadcrumbs.
            'next_breadcrumbs_prepend' => '',
            'next_breadcrumbs_separator' => '&gt;',
        ],
    ],
];

$isBelow14 = version_compare(\Omeka\Module::VERSION, '1.4.0', '<');
if ($isBelow14) {
    // $config['view_helpers']['invokables']['userBar'] = View\Helper\UserBar::class;
    $config['view_helpers']['factories']['logger'] = Service\ViewHelper\LoggerFactory::class;
}

// Avoid to override existing modules.

if (!file_exists(dirname(dirname(__DIR__)) . '/BlockPlus/Module.php')) {
    // Deprecated Use module BlockPlus.
    $config['block_layouts'] = [
        'invokables' => [
            'searchForm' => Site\BlockLayout\SearchForm::class,
        ],
        'factories' => [
            'simplePage' => Service\BlockLayout\SimplePageFactory::class,
        ],
    ];
    $config['form_elements']['invokables'][Form\SearchFormFieldset::class] = Form\SearchFormFieldset::class;
    $config['form_elements']['invokables'][Form\SimplePageFieldset::class ] = Form\SimplePageFieldset::class;
    $config['next']['block_settings'] = [
        'searchForm' => [
            'heading' => '',
        ],
        'simplePage' => [
            'page' => null,
        ],
    ];
}

return $config;
