<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class Product extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Product');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'active',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => '1',
            ),
        ));
        $this->add(array(
            'name' => 'deleted',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => '0',
            ),
        ));
        $this->add(array(
            'name' => 'position',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Position...',
            ),
            'options' => array(
                'label' => 'Position',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'visible',
            'attributes' => array(
                'class' => 'checkbox',
            ),
            'options' => array(
                'label' => 'Visible',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Name...',
            ),
            'options' => array(
                'label' => 'Name',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'shortDescription',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Short description...',
            ),
            'options' => array(
                'label' => 'Short Description',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        /*$this->add(array(
            'name' => 'longDescription',
            'attributes' => array(
                'type'  => 'textarea',
                'class' => 'form-control form-element',
                'placeholder' => 'Long description...',
            ),
            'options' => array(
                'label' => 'Long Description',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));*/
        
        $this->add(array( 
            'name' => 'longDescription', 
            #'type' => 'Zend\Form\Element\Textarea', 
            'type' => 'CKEditorModule\Form\Element\CKEditor',
            'attributes' => array( 
                'placeholder' => 'Long Description...',
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Long Description', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'ckeditor' => array(
                    // add anny config you would normaly add via CKEDITOR.editorConfig
                    'language' => 'en',
                    #'uiColor' => '#428bca',
                ),
            ), 
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'taxId',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Tax Group',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'personalized',
            'attributes' => array(
                'class' => 'checkbox',
            ),
            'options' => array(
                'label' => 'Personalized',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}