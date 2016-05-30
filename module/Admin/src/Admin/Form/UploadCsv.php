<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class UploadCsv extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('UploadCsv');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'bankaccount_id',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Bank Account',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array( 
            'name' => 'csv-upload', 
            'type' => 'file', 
            'attributes' => array(
                'class' => 'form-element',
            ), 
            'options' => array( 
                'label' => 'CSV File',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        
        $this->add(array(
            'name' => 'separator',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Separator...',
                'class' => 'form-control form-element',
                'value' => ',',
            ),
            'options' => array(
                'label' => 'Position',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array( 
            'name' => 'csrf', 
            'type' => 'Zend\Form\Element\Csrf', 
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Upload',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}