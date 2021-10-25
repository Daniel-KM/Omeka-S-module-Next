<?php declare(strict_types=1);

namespace Next;

return [
    'listeners' => [
        Mvc\MvcListeners::class,
    ],
    'service_manager' => [
        'invokables' => [
            Mvc\MvcListeners::class => Mvc\MvcListeners::class,
            'Omeka\ViewApiJsonRenderer' => View\Renderer\ApiJsonRenderer::class,
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
            'thumbnailUrl' => View\Helper\ThumbnailUrl::class,
        ],
        'factories' => [
            'browsePreviousNext' => Service\ViewHelper\BrowsePreviousNextFactory::class,
            'defaultSiteSlug' => Service\ViewHelper\DefaultSiteSlugFactory::class,
            'nextResource' => Service\ViewHelper\NextResourceFactory::class,
            'previousResource' => Service\ViewHelper\PreviousResourceFactory::class,
            'publicResourceUrl' => Service\ViewHelper\PublicResourceUrlFactory::class,
            'userSiteSlugs' => Service\ViewHelper\UserSiteSlugsFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
        'factories' => [
            Form\Element\SitesPageSelect::class => Service\Form\Element\SitesPageSelectFactory::class,
            Form\SettingsFieldset::class => Service\Form\SettingsFieldsetFactory::class,
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
            'next_prevnext_disable' => false,
        ],
        'site_settings' => [
            'next_items_order_for_itemsets' => [],
            'next_prevnext_items_query' => '',
            'next_prevnext_item_sets_query' => '',
            'next_breadcrumbs_crumbs' => [
                'home',
                'collections',
                'itemset',
                'current',
            ],
            'next_breadcrumbs_prepend' => [],
            'next_breadcrumbs_collections_url' => '',
            'next_breadcrumbs_separator' => '&gt;',
            'next_breadcrumbs_homepage' => false,
        ],
    ],
];
