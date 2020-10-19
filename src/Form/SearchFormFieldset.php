<?php
namespace Next\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SearchFormFieldset extends Fieldset
{
    public function init()
    {
        $this
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][heading]',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Block title', // @translate
                    'info' => 'Heading for the block, if any.', // @translate
                ],
            ]);
    }
}
