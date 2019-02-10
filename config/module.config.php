<?php
namespace Next;

return [
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
            'citation' => View\Helper\Citation::class,
            'currentSite' => View\Helper\CurrentSite::class,
            'isHomePage' => View\Helper\IsHomePage::class,
            'lastBrowsePage' => View\Helper\LastBrowsePage::class,
            'searchFilters' => View\Helper\SearchFilters::class,
            'userBar' => View\Helper\UserBar::class,
        ],
        'factories' => [
            'defaultSiteSlug' => Service\ViewHelper\DefaultSiteSlugFactory::class,
            'logger' => Service\ViewHelper\LoggerFactory::class,
            'nextResource' => Service\ViewHelper\NextResourceFactory::class,
            'previousResource' => Service\ViewHelper\PreviousResourceFactory::class,
            'publicResourceUrl' => Service\ViewHelper\PublicResourceUrlFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
        ],
        'factories' => [
            'simplePage' => Service\BlockLayout\SimplePageFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\SimplePageBlockForm::class => Form\SimplePageBlockForm::class,
        ],
        'factories' => [
            Form\Element\SitePageSelect::class => Service\Form\Element\SitePageSelectFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
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
        'block_settings' => [
            'simplePage' => [
                'page' => null,
            ],
        ],
    ],
];
