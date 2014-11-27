<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cart\Model;

use Zend\Db\Adapter\Adapter;

class OrderTable extends Table {
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->table = 'Order';
        $this->entityClass = '\Cart\Model\Entity\Order';
    }
}