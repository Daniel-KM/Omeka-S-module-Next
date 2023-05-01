<?php declare(strict_types=1);

namespace Next\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Next Module'; // @translate

    protected $elementGroups = [
        'next' => 'Next Module', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'next')
            ->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => 'next_prevnext_disable',
                'type' => Element\Checkbox::class,
                'options' => [
                    'element_group' => 'next',
                    'label' => 'Disable previous/next', // @translate
                    'info' => 'An issue exists on some versions of mysql database (mariadb is working fine).', // @translate
                ],
                'attributes' => [
                    'id' => 'next-prevnext-disable',
                ],
            ])
        ;
    }
}
