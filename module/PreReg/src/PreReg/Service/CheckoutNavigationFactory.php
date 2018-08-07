<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use Zend\Navigation\Service\AbstractNavigationFactory;

/**
 * Main navigation factory.
 */
class CheckoutNavigationFactory extends AbstractNavigationFactory
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'checkout_nav';
    }
}
