<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class ManualMatch extends Form
{
    public function __construct()
    {
        parent::__construct('ManualMatch');
        $this->setAttribute('method', 'post');
        
        $this->add(array( 
            'name' => 'orders', 
            'type' => 'Zend\Form\Element\MultiCheckbox', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Order',
                /*'value_options' => array(
                    '0' => 'Checkbox', 
                    '1' => 'Checkbox', 
                ),*/
            ), 
        ));
        
        $this->add(array( 
            'name' => 'statements', 
            'type' => 'Zend\Form\Element\MultiCheckbox', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'BankStatements',
                /*'value_options' => array(
                    '0' => 'Checkbox', 
                    '1' => 'Checkbox', 
                ),*/
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
                'value' => 'Do Match',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}