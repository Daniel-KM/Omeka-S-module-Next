<?php
namespace Next;

$config = [
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
            'searchFilters' => View\Helper\SearchFilters::class,
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
        ],
        'factories' => [
            Form\Element\SitesPageSelect::class => Service\Form\Element\SitesPageSelectFactory::class,
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
            'next_breadcrumbs_property_itemset' => '',
        ],
        'site_settings' => [
            'next_search_used_terms' => true,
            'next_breadcrumbs_crumbs' => [
                'home',
                // 'homepage',
                'current',
                'itemset',
            ],
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
