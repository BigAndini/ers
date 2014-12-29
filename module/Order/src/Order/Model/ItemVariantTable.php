<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Order\Model;

use Zend\Db\Adapter\Adapter;

class ItemTable extends Table {
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->table = 'Item';
        $this->entityClass = '\Order\Model\Entity\Item';
    }
}