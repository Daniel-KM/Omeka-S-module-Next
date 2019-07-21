<?php
namespace Next\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    public function init()
    {
        $this->setLabel('Module Next'); // @translate

        $this->add([
            'type' => Element\Checkbox::class,
            'name' => 'search_used_terms',
            'options' => [
                'label' => 'List only used properties and resources classes', // @translate
                'info' => 'Restrict the list of properties and resources classes to the used ones in advanced search form (for properties, when option "templates" is not used).', // @translate
            ],
        ]);
    }
}
