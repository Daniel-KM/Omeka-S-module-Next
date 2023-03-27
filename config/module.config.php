<?php declare(strict_types=1);

namespace Next;

$conf = [
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
            'currentSite' => View\Helper\CurrentSite::class,
            'isHomePage' => View\Helper\IsHomePage::class,
            'itemSetPosition' => View\Helper\ItemSetPosition::class,
            'lastBrowsePage' => View\Helper\LastBrowsePage::class,
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
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
        'factories' => [
            Form\Element\SitesPageSelect::class => Service\Form\Element\SitesPageSelectFactory::class,
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
            'next_prevnext_disable' => false,
        ],
        'site_settings' => [
            'next_items_order_for_itemsets' => [],
            'next_prevnext_items_query' => '',
            'next_prevnext_item_sets_query' => '',
        ],
    ],
];

$isV4 = version_compare(\Omeka\Module::VERSION, '4', '>=');

if ($isV4) {
    unset($conf['view_helpers']['invokables']['currentSite']);
}

return $conf;
