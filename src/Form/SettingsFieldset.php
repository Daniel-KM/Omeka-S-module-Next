<?php declare(strict_types=1);

namespace Next\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Next Module'; // @translate

    public function init(): void
    {
        $this
            ->setAttribute('id', 'next')
            ->add([
                'name' => 'next_prevnext_disable',
                'type' => Element\Checkbox::class,
                'options' => [
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
