<?php declare(strict_types=1);

namespace Next;

$conf = [
    'service_manager' => [
        'invokables' => [
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
        ],
        'factories' => [
            'defaultSiteSlug' => Service\ViewHelper\DefaultSiteSlugFactory::class,
            'publicResourceUrl' => Service\ViewHelper\PublicResourceUrlFactory::class,
            'userSiteSlugs' => Service\ViewHelper\UserSiteSlugsFactory::class,
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
    ],
];

$isV4 = version_compare(\Omeka\Module::VERSION, '4', '>=');

if ($isV4) {
    unset($conf['view_helpers']['invokables']['currentSite']);
}

return $conf;
