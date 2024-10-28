<?php declare(strict_types=1);

namespace Next;

return [
    'service_manager' => [
        'factories' => [
            'Omeka\ViewApiJsonRenderer' => Service\ViewApiJsonRendererFactory::class,
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
            'defaultSiteSlug' => View\Helper\DefaultSiteSlug::class,
            'itemSetPosition' => View\Helper\ItemSetPosition::class,
        ],
        'factories' => [
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
