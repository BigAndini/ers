<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;


class CreditCard extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('CreditCard');
        
        $this->setAttribute('method', 'post'); 
        
        /*<form method="post" action="https://ipayment.de/merchant/99999/processor/2.0/">
        <!—- Base Parameter -->
        <input type="hidden" name="trxuser_id" value="99999">
        <input type="hidden" name="trxpassword" value="0">
        <!—- Amount and Currency for Payment -->
        <input type="hidden" name="trx_amount" value="12989">
        <input type="hidden" name="trx_currency" value="EUR">
        <!—- Paymenttyp: CC to make creditcard payment -->
        <input type="hidden" name="trx_paymenttyp" value="cc">
        <!—- URL and Parameter for Redirect back to Shop -->
        <input type="hidden" name="redirect_url" value="<?php $this->url('order', array('action' => 'thankyou')); ?>">
        <input type="hidden" name="silent" value="1">
        <input type="hidden" name="silent_error_url" value="<?php $this->url('payment', array('action' => 'failed')); ?>">
        <!—- Credit card data fields -->
        Cardholder name: <input type="text" name="addr_name" value=""><br>
        Credit card no.: <input type="text" name="cc_number" value=""><br>
        Card Check Code: <input type="text" name="cc_checkcode" value=""><br>
        Card Expire date: <select name="cc_expdate_month">
        <?php for($i=1; $i<=12; $i++): ?>
            <option><?php echo sprintf('%02d', $i); ?></option>
        <?php endfor; ?>
        </select>
        &nbsp;/&nbsp;
        <select name="cc_expdate_yearyear">
            <?php for($i=date('Y'); $i<=(date('Y')+15); $i++): ?>
            <option><?php echo $i; ?></option>
            <?php endfor; ?>
        </select><br />
        <!—- Submit Button -->
        <input type="submit" name="form_submit" value="Process payment">*/
        
        $this->add(array(
            'name' => 'trxuser_id',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => '99999',
            ),
        ));
        
        $this->add(array(
            'name' => 'trxpassword',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => '0',
            ),
        ));
        
        $this->add(array(
            'name' => 'trx_amount',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'trx_currency',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'trx_paymenttyp',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => 'cc',
            ),
        ));
        
        $this->add(array(
            'name' => 'redirect_url',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'silent',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => '1'
            ),
        ));
        
        $this->add(array(
            'name' => 'silent_error_url',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'hidden_trigger_url',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'trx_securityhash',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'shopper_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'advanced_strict_id_check',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => '1',
            ),
        ));
        
        $this->add(array( 
            'name' => 'addr_name', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Cardholder name...', 
                'required' => 'required', 
                'class' => 'form-control form-element',
                'autocomplete' => 'off',
            ), 
            'options' => array( 
                'label' => 'Cardholder name', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        
        $this->add(array( 
            'name' => 'cc_typ', 
            'type' => 'Zend\Form\Element\Select', 
            'attributes' => array( 
                'required' => 'required', 
                'class' => 'form-control form-element',
                'autocomplete' => 'off',
            ), 
            'options' => array( 
                'label' => 'Credit Card Type', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        
        $this->add(array(
            'name' => 'ignore_cc_typ_mismatch',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => '1',
            ),
        ));
        
        $this->add(array( 
            'name' => 'cc_number', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Credit Card Number...', 
                'required' => 'required', 
                'class' => 'form-control form-element',
                'autocomplete' => 'off',
            ), 
            'options' => array( 
                'label' => 'Credit Card Number', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        
        $this->add(array( 
            'name' => 'cc_checkcode', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
                'placeholder' => 'Card Check Code...', 
                'required' => 'required', 
                'class' => 'form-control form-element',
                'maxlength' => '3',
                'autocomplete' => 'off',
            ), 
            'options' => array( 
                'label' => 'Card Check Code', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        
        $this->add(array( 
            'name' => 'cc_expdate_month', 
            'type' => 'Zend\Form\Element\Select', 
            'attributes' => array( 
                'required' => 'required', 
                'class' => 'form-control',
                'autocomplete' => 'off',
            ),
            
            'options' => array( 
                'label' => 'Expire Date', 
                /*'label_attributes' => array(
                    'class'  => '',
                ),*/
                'option_values' => array(
                    '1' => '01',
                    '2' => '02',
                    '3' => '03',
                    '4' => '04',
                    '5' => '05',
                    '6' => '06',
                    '7' => '07',
                    '8' => '08',
                    '9' => '09',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ),
            ), 
        ));
 
        $this->add(array( 
            'name' => 'cc_expdate_year', 
            'type' => 'Zend\Form\Element\Select', 
            'attributes' => array( 
                'required' => 'required', 
                'class' => 'form-control',
                'autocomplete' => 'off',
            ),
            'options' => array( 
                'label' => ' ', 
                /*'label_attributes' => array(
                    'class'  => '',
                ),*/
                'option_values' => array(
                    '2015' => '2015',
                    '2016' => '2016',
                    '2017' => '2017',
                    '2018' => '2018',
                    '2019' => '2019',
                    '2020' => '2020',
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
                'value' => 'pay now',
                'id' => 'submitbutton',
                'class' => 'btn btn-success media-object',
            ),
        ));
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) 
        { 
            $inputFilter = new InputFilter(); 
            $factory = new InputFactory();             

            /*$inputFilter->add($factory->createInput([ 
                'name' => 'firstname', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'surname', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                ), 
            ]));*/ 

            /*$inputFilter->add($factory->createInput([ 
                'name' => 'birthday', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array(
                        'name' => 'Between',
                        'options' => array(
                        ),
                    ),
                ), 
            ]));*/

            /*$inputFilter->add($factory->createInput([ 
                'name' => 'email', 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array ( 
                        'name' => 'EmailAddress', 
                        'options' => array( 
                            'messages' => array( 
                                'emailAddressInvalidFormat' => 'Email address format is not invalid', 
                            ) 
                        ), 
                    ), 
                    array ( 
                        'name' => 'NotEmpty', 
                        'options' => array( 
                            'messages' => array( 
                                'isEmpty' => '', 
                            ) 
                        ), 
                    ), 
                ), 
            ]));*/
 
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    }
}