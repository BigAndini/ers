<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model;

use Zend\Db\Adapter\Adapter;


class ProductPriceTable extends Table {
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->table = 'ProductPrice';
        $this->entityClass = '\PreReg\Model\Entity\ProductPrice';
    }
    
    public function save($entity) {
        error_log('in ProductPriceTable save');
        
        #$time = \DateTime::createFromFormat( 'd.m.Y H:i', $this->validFrom )->getTimestamp();
        #$this->validFrom = new \DateTime( date( 'Y-m-d H:i', $time ) )->format('Y-m-d H:i:s');
        
        #$entity->validFrom = \DateTime::createFromFormat( 'd.m.Y H:i', $entity->validFrom )->format('Y-m-d H:i:s');
        
        #$dt = new \DateTime( $entity->validFrom );
        #$entity->validFrom = $dt->format('Y-m-d H:i:s');
        
        parent::save($entity);
    }
}