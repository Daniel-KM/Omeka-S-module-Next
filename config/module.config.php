<?php declare(strict_types=1);

namespace Next;

return [
    'service_manager' => [
        'factories' => [
            'Omeka\ViewApiJsonRenderer' => Service\ViewApiJsonRendererFactory::class,
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'itemSetPosition' => View\Helper\ItemSetPosition::class,
        ],
        'factories' => [
            'publicResourceUrl' => Service\ViewHelper\PublicResourceUrlFactory::class,
            'userSiteSlugs' => Service\ViewHelper\UserSiteSlugsFactory::class,
        ],
    ],
    'next' => [
    ],
];
