<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Form;

use Zend\Form\Form;


class Search extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Search');
        $this->setAttribute('method', 'get');
        
        $this->add(array(
            'name' => 'q',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'search',
                'class' => 'form-control onsite-search-box',
                'placeholder' => 'Code / Name / Date of birth / E-Mail / ID ...',
            ),
            'options' => array(
                'label' => '',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        /*$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Search',
                'class' => 'btn btn-lg btn-success',
            ),
        ));*/
    }
}