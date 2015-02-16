<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Validator;

use Zend\Validator\AbstractValidator;

class PurchaserEmail extends AbstractValidator
{
    const INVALID_COUNTRY = 'countryInvalid';
    
    protected $messageTemplates = array(
        self::INVALID_COUNTRY => "Country code is invalid!",
    );
    
    protected $countries = array();
    
    public function setCountries(array $countries)
    {
        $this->countries = $countries;
    }
    
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        
        if (!in_array($value, $this->countries)) {
            $this->error(self::INVALID_COUNTRY);
            return false;
        }
        
        return true;
    }
} 