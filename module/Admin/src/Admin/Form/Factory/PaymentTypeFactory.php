<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Admin\Form;

class PaymentTypeFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $form = new Form\PaymentType();
        $form->get('submit')->setValue('Save');

        $optionService = $serviceLocator->get('ErsBase\Service\OptionService');
        #$deadlineOptions = $this->buildDeadlineOptions();
        $deadlineOptions = $optionService->getDeadlineOptions();
        $form->get('active_from_id')->setAttribute('options', $deadlineOptions);
        $form->get('active_until_id')->setAttribute('options', $deadlineOptions);
        #$form->get('active_from_id')->setValue(0);
        #$form->get('active_until_id')->setValue(0);
        $currencyOptions = $optionService->getCurrencyOptions();
        $form->get('currency_id')->setAttribute('options', $currencyOptions);

        $typeOptions = [
            [
                'value' => '',
                'label' => 'Select type ...',
                'disabled' => true,
                'selected' => true,
            ],
            [
                'value' => 'sepa',
                'label' => 'Sepa Bank Account',
            ],
            [
                'value' => 'ukbt',
                'label' => 'UK Bank Account',
            ],
            [
                'value' => 'ipayment',
                'label' => 'iPayment Account',
            ],
            [
                'value' => 'paypal',
                'label' => 'Paypal Account',
            ],
        ];
        $form->get('type')->setAttribute('options', $typeOptions);

        return $form;
    }
}