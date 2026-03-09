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
    'next' => [
    ],
];
