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

class AccountSepabankDetail extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('account-sepabank-detail');

        $this->setHydrator(new DoctrineHydrator($objectManager));

        /*
         * iban
         * bic
         * owner
         * ownerAddress1
         * ownerAddress2
         * ownerAddress3
         * ownerAddress4
         * bankAddress1
         * bankAddress2
         * bankAddress3
         * bankAddress4
         * bankName
         * bankCountry
         */
        
        
        $this->add(array(
            'name' => 'iban',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'NL84 INGB 0007 8721 92...',
            ),
            'options' => array(
                'label' => 'IBAN',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'bic',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'INGBNL2A...',
            ),
            'options' => array(
                'label' => 'BIC',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'owner',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Stichting European Juggling Association...',
            ),
            'options' => array(
                'label' => 'Owner',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        
        $this->add(array(
            'name' => 'bank_name',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'ING Bank N.V....',
            ),
            'options' => array(
                'label' => 'Bank Name',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'bank_country',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Bank Country',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'owner_address1',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Owner Address Line 1',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'owner_address2',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Owner Address Line 2',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'owner_address3',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Owner Address Line 3',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'owner_address4',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Owner Address Line 4',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'bank_address1',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Bank Address Line 1',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'bank_address2',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Bank Address Line 2',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'bank_address3',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Bank Address Line 3',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'bank_address4',
            'attributes' => array(
                #'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Netherlands...',
            ),
            'options' => array(
                'label' => 'Bank Address Line 4',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
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
}