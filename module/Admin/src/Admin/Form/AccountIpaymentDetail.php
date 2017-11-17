<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Doctrine\Common\Persistence\ObjectManager;
use ErsBase\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class AccountIpaymentDetail extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('account-ipayment-detail');

        $this->setHydrator(new DoctrineHydrator($objectManager));

        /*
         * account_id
         * trxuser_id
         * trx_currency
         * trxpassword
         * sec_key
         * action
         */
        
        $this->addAccountId();
        $this->addTrxuserId();
        $this->addTrxCurrency();
        $this->addTrxpassword();
        $this->addSecKey();
        $this->addAction();
        
        
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Send',
                'class' => 'btn btn-success',
            ),
        ));
    }
    
    private function addAccountId() {
        $this->add(array(
            'name' => 'account_id',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => '12345...',
            ),
            'options' => array(
                'label' => 'Account Id',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
    }
    private function addTrxuserId() {
        $this->add(array(
            'name' => 'trxuser_id',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => '12345...',
            ),
            'options' => array(
                'label' => 'Trxuser Id',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
    }
    private function addTrxCurrency() {
        $this->add(array(
            'name' => 'trx_currency',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'EUR...',
            ),
            'options' => array(
                'label' => 'Trx Currency',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
    }
    private function addTrxpassword() {
        $this->add(array(
            'name' => 'trxpassword',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => '0123456789...',
            ),
            'options' => array(
                'label' => 'Trxpassword',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
    }
    private function addSecKey() {
        $this->add(array(
            'name' => 'sec_key',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => '0123456789ABCDEFG...',
            ),
            'options' => array(
                'label' => 'Security Key',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
    }
    private function addAction() {
        $this->add(array(
            'name' => 'action',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'https://ipayment.de/merchant/%account_id%/processor/2.0/...',
            ),
            'options' => array(
                'label' => 'Action (Change only if you really know what you are doing)',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
    }
}