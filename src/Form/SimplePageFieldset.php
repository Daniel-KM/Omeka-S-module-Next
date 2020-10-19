<?php declare(strict_types=1);
namespace Next\Form;

use Laminas\Form\Fieldset;
use Next\Form\Element\SitesPageSelect;

class SimplePageFieldset extends Fieldset
{
    public function init(): void
    {
        $this->add([
            'name' => 'o:block[__blockIndex__][o:data][page]',
            'type' => SitesPageSelect::class,
            'options' => [
                'label' => 'Page', // @translate
                'info' => 'Private sites are marked with a "*". If a private page is selected, it will be hidden on the public site. The current page and recursive pages are forbidden.', // @translate
            ],
            'attributes' => [
                'id' => 'page',
                'required' => true,
                'class' => 'chosen-select',
            ],
        ]);
    }
}
