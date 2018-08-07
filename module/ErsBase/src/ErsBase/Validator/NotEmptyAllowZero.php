<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Validator;

use Zend\Validator\NotEmpty;

class NotEmptyAllowZero extends NotEmpty {

    public function isValid( $value ) {

        $type = $this->getType();

        // allow zero float
        if($type >= self::FLOAT && $value == 0.0) {
            return true;
        }
        // allow integer zero
        if ($type >= self::INTEGER && $value == 0) {
            return true;
        }

        // go on with zend validator
        return parent::isValid( $value );
    }

}