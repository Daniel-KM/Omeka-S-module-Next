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
            'currentSite' => View\Helper\CurrentSite::class,
            'isHomePage' => View\Helper\IsHomePage::class,
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
    // Deprecated Use module BlockPlus.
    'block_layouts' => [
        'invokables' => [
            'searchForm' => Site\BlockLayout\SearchForm::class,
        ],
        'factories' => [
            'simplePage' => Service\BlockLayout\SimplePageFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\SimplePageFieldset::class => Form\SimplePageFieldset::class,
            Form\SearchFormFieldset::class => Form\SearchFormFieldset::class,
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
        ],
        'site_settings' => [
            'search_used_terms' => true,
        ],
        'block_settings' => [
            'searchForm' => [
                'heading' => '',
            ],
            'simplePage' => [
                'page' => null,
            ],
        ],
    ],
];

$isBelow14 = version_compare(\Omeka\Module::VERSION, '1.4.0', '<');
if ($isBelow14) {
    // $config['view_helpers']['invokables']['userBar'] = View\Helper\UserBar::class;
    $config['view_helpers']['factories']['logger'] = Service\ViewHelper\LoggerFactory::class;
}

return $config;
