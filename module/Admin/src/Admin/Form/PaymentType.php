<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class PaymentType extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('PaymentType');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'position', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Position ...',
                'class' => 'form-control form-element',
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
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Payment Type Name ...', 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Name', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
        
        $this->add(array(
            'name' => 'type',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control form-element'
            ),
            'options' => array(
                'label' => 'Type',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'empty_option' => 'Select type ...',
                'value_options' => [
                    ['value' => 'BankTransfer', 'label' => 'Bank transfer'],
                    ['value' => 'Cheque', 'label' => 'Cheque'],
                    ['value' => 'PayPal', 'label' => 'PayPal'],
                    ['value' => 'CreditCard', 'label' => 'Credit card'],
                    ['value' => 'IPayment', 'label' => 'Credit card (iPayment)'],
                ]
            ),
        ));
        
        $this->add(array( 
            'name' => 'short_description', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Short Description ...', 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Short Description', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'long_description', 
            #'type' => 'Zend\Form\Element\Textarea', 
            'type' => 'CKEditorModule\Form\Element\CKEditor',
            'attributes' => array( 
                'placeholder' => 'Long Description ...',
                /*'class' => 'form-control form-element',*/
            ), 
            'options' => array( 
                'label' => 'Long Description', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'ckeditor' => array(
                    // add any config you would normaly add via CKEDITOR.editorConfig
                    'language' => 'en',
                    #'uiColor' => '#428bca',
                ),
            ), 
        ));
 
        $this->add(array( 
            'name' => 'explanation', 
            #'type' => 'Zend\Form\Element\Textarea', 
            'type' => 'CKEditorModule\Form\Element\CKEditor',
            'attributes' => array( 
                'placeholder' => 'Explanation...',
                /*'class' => 'form-control form-element',*/
            ), 
            'options' => array( 
                'label' => 'Explanation', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'ckeditor' => array(
                    // add any config you would normaly add via CKEDITOR.editorConfig
                    'language' => 'en',
                    #'uiColor' => '#428bca',
                ),
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'fix_fee', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Fix Fee ...',
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Fix Fee (default: 0)', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'percentage_fee', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Percentage Fee ...',
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Percentage Fee (default: 0)', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array(
            'name' => 'active_from_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'active from',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'active_until_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'active until',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array( 
            'name' => 'days2pay', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Days until Payment ...', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Days until Payment (default: 0)', 
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
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}