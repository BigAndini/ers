<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;


class UserTable extends Table {
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->table = 'User';
        $this->entityClass = '\PreReg\Model\Entity\User';
    }
    
    /*public function fetchAll($order = '') {
            
        $resultSet = $this->select(function (Select $select) {
                    #$select->order($order);
                    $select->order('created ASC');
                });
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Entity\User();
            foreach($row as $key => $val) {
                $entity->$key = $val;
            }
            $entities[] = $entity;
        }
        return $entities;
    }*/
}