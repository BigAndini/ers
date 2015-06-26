<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class DownloadEticket extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Agegroup');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        /*$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'language',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Language',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));*/
        
        $this->add(array( 
            'name' => 'csrf', 
            'type' => 'Zend\Form\Element\Csrf', 
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Download',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}