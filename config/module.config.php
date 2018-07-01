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
            'mediaSize' => Service\ViewHelper\MediaSizeFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'browsePreview' => Site\BlockLayout\BrowsePreview::class,
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
        'config' => [
        ],
        'block_settings' => [
        ],
    ],
];
