<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Order\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

#abstract class Table extends AbstractTableGateway {
class Table extends AbstractTableGateway implements \Zend\ServiceManager\ServiceLocatorAwareInterface {
    protected $adapter;
    protected $table;
    protected $entityClass;
    protected $sm;

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function fetchAll($order = '') {
        if($order == '') {
            $order = 'created ASC';
        }
        error_log('order: '.$order);
        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        
        $select->order($order);

        #$where = new  Where();
        #$where->equalTo('album_id', $id) ;
        #$select->where($where);

        //you can check your query by echo-ing :
        // echo $select->getSqlString();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new $this->entityClass();
            foreach($row as $key => $val) {
                $entity->$key = $val;
            }
            $entities[] = $entity;
        }
        return $entities;
        
        
        
        
        

        
        /*$resultSet = $this->select(function (Select $select) {
                    #$select->order($order);
                    $select->order('created ASC');
                });
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new $this->entityClass();
            foreach($row as $key => $val) {
                $entity->$key = $val;
            }
            $entities[] = $entity;
        }
        return $entities;*/
    }
    
    public function getById($id) {
        $row = $this->select(array('id' => (int) $id))->current();
        if (!$row) {
            return false;
        }

        $data = array();
        foreach($row as $key => $val) {
            $data[$key] = $val;
        }
        $entity = new $this->entityClass($data);
        return $entity;
    }
    
     public function getByField($field, $value, $order = 'created ASC') {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        
        $select->order($order);

        $where = new  Where();
        $where->equalTo($field, $value) ;
        $select->where($where);

        //you can check your query by echo-ing :
        // echo $select->getSqlString();
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        #$result = $this->select(array($field => $value))->current();
        if (!$result) {
            error_log('no result set');
            return false;
        }

        $entities = array();
        foreach($result as $row) {
            $data = array();
            foreach($row as $key => $val) {
                $data[$key] = $val;
            }
            $entity = new $this->entityClass($data);
            $entities[] = clone $entity;
            unset($entity);
        }
        
        return $entities;
    }
    
    public function save($entity) {
        error_log('in Table save');
        /*$vars = $entity->getEntityVars();
        error_log('save vars: '.count($vars));
        foreach($vars as $v) {
            error_log('var name: '.$v.' value: '.$entity->$v);
        }*/
        $data = array();
        foreach($entity->getEntityVars() as $key) {
            if($key == 'sm') {
                continue;
            }
            if(is_object($entity->$key)) {
                if(get_class($entity->$key) == 'DateTime') {
                    $data[$key] = $entity->$key->format("Y-m-d H:i:s");
                }
            } elseif(is_string($entity->$key)) {
                $data[$key] = $entity->$key;
            } elseif(!isset($entity->$key)) {
                $data[$key] = null;
            } else {
                if($key != 'created' && $key != 'updated') {
                    error_log('Entity->save: unrecognized type of key '.$key);
                }
            }
        }

        $id = (int) $entity->id;

        if ($id == 0) {
            $data['created'] = date("Y-m-d H:i:s");
            if (!$this->insert($data)) {
                return false;
            }
            return $this->getLastInsertValue();
        }
        elseif ($this->getById($id)) {
            if (!$this->update($data, array('id' => $id))) {
                return false;
            }
            return $id;
        } else {
            return false;
        }
    }
    
    public function removeById($id) {
        return $this->delete(array('id' => (int) $id));
    }

    public function getServiceLocator() {
        return $this->sm;
    }

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $this->sm = $serviceLocator;
    }
}