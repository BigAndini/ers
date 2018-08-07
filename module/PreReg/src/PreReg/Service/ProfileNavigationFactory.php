<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use Zend\Navigation\Service\AbstractNavigationFactory;

/**
 * Top navigation factory.
 */
class ProfileNavigationFactory extends AbstractNavigationFactory
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'profile_nav';
    }
}
