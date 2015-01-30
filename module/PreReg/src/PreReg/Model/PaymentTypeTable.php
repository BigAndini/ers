<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model;

use Zend\Db\Adapter\Adapter;

class PaymentTypeTable extends Table {
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->table = 'PaymentType';
        $this->entityClass = '\PreReg\Model\Entity\PaymentType';
    }
}