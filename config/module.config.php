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
        ],
        'factories' => [
            'logger' => Service\ViewHelper\LoggerFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
        ],
        'factories' => [
        ],
    ],
    'form_elements' => [
        'invokables' => [
        ],
        'factories' => [
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
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
        ],
    ],
];
