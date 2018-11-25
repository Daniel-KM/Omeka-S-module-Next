<?php
namespace Next\Form;

use Next\Form\Element\SitePageSelect;
use Zend\Form\Form;

class SimplePageBlockForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'o:block[__blockIndex__][o:data][page]',
            'type' => SitePageSelect::class,
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
