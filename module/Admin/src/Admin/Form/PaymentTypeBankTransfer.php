<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;


class PaymentTypeBankTransfer extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('PaymentTypeBankTransfer');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'ordering', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Order...',
            ), 
            'options' => array( 
                'label' => 'Order', 
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'name', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Payment Type Name...', 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Name', 
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'shortDescription', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Short Description...', 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Short Description', 
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'longDescription', 
            'type' => 'Zend\Form\Element\Textarea', 
            'attributes' => array( 
                'placeholder' => 'Long Description...', 
            ), 
            'options' => array( 
                'label' => 'Long Description', 
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'fixFee', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Fix Fee...', 
            ), 
            'options' => array( 
                'label' => 'Fix Fee (default: 0)', 
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'percentageFee', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Percentage Fee...', 
            ), 
            'options' => array( 
                'label' => 'Percentage Fee (default: 0)', 
            ), 
        )); 
 
        $this->add(array(
            'name' => 'activeFrom_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required', 
            ),
            'options' => array(
                'label' => 'active from',
            ),
        ));
        $this->add(array(
            'name' => 'activeUntil_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required', 
            ),
            'options' => array(
                'label' => 'active until',
            ),
        ));
        /*$this->add(array( 
            'name' => 'activeFrom', 
            'type' => 'Zend\Form\Element\DateTime', 
            'attributes' => array( 
                'placeholder' => 'Active From...', 
                'required' => 'required', 
                'class' => 'datetimepicker',
            ), 
            'options' => array( 
                'label' => 'Active From', 
            ), 
        ));
        $this->get('activeFrom')->setFormat('Y-m-d H:i:s');
 
        $this->add(array( 
            'name' => 'activeUntil', 
            'type' => 'Zend\Form\Element\DateTime', 
            'attributes' => array( 
                'placeholder' => 'Active Until...', 
                'required' => 'required', 
                'class' => 'datetimepicker',
            ), 
            'options' => array( 
                'label' => 'Active Until', 
            ), 
        ));
        $this->get('activeUntil')->setFormat('Y-m-d H:i:s');*/
 
        $this->add(array( 
            'name' => 'days2pay', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Days until Payment...', 
            ), 
            'options' => array( 
                'label' => 'Days until Payment (default: 0)', 
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