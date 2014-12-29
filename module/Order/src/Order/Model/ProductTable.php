<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Order\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class ProductTable extends Table {
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->table = 'Product';
        $this->entityClass = '\Order\Model\Entity\Product';
    }
    
    public function fetchAll($order = '') {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        
        //you can check your query by echo-ing :
        #error_log('SQL: '.$select->getSqlString());
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $Products = array();
        foreach ($result as $row) {
            $Product = new Entity\Product;
            
            foreach($row as $key => $val) {
                $Product->$key = $val;
            }

            $this->loadProductVariants($Product);
            $this->loadProductPrices($Product);
            error_log('loaded '.count($Product->getPrices()).' prices for product id: '.$Product->id);
            
            $Products[] = $Product; 
        }
        return $Products;
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
        
        $this->loadProductVariants($entity);
        $this->loadProductPrices($entity);

        return $entity;
    }
    
    public function save() {
        throw new Exception('In this context it is not possible to save a Product Entity', 500, null);
    }
    
    private function loadProductVariants(Entity\Product & $Product) {
        if($Product->id <= 0) {
            throw new Exception('Unable to load ProductVariants for Product with id 0', 500, null);
        }
        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('ProductVariant');
        
        $where = new  Where();
        $where->equalTo('Product_id', $Product->id) ;
        $select->where($where);
        
        #error_log('SQL: '.$select->getSqlString());
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $ProductVariants = array();
        foreach ($result as $row) {
            $ProductVariant = new Entity\ProductVariant;
            foreach($row as $key => $val) {
                $ProductVariant->$key = $val;
            }
            $this->loadProductVariantValues($ProductVariant);
            $ProductVariants[] = $ProductVariant;   
        }
        #return $ProductVariants;
        
        return $Product->setVariants($ProductVariants);
    }
    
    private function loadProductVariantValues(Entity\ProductVariant & $ProductVariant) {
        if($ProductVariant->id <= 0) {
            throw new Exception('Unable to load ProductVariantValues for ProductVariant with id 0', 500, null);
        }
        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('ProductVariantValue');
        
        $where = new  Where();
        $where->equalTo('ProductVariant_id', $ProductVariant->id) ;
        $select->where($where);
        
        #error_log('SQL: '.$select->getSqlString());
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $ProductVariantValues = array();
        foreach ($result as $row) {
            $ProductVariantValue = new Entity\ProductVariantValue;
            foreach($row as $key => $val) {
                $ProductVariantValue->$key = $val;
            }            
            $ProductVariantValues[] = $ProductVariantValue;   
        }
        #return $ProductVariants;
        
        return $ProductVariant->setValues($ProductVariantValues);
    }
    
    private function loadProductPrices(Entity\Product & $Product) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('ProductPrice');
        $date = new \DateTime();
        error_log('price date: '.$date->format('Y-m-d H:i:s'));
        $select->where
                ->equalTo('Product_id', $Product->id);
                #->and
                #->greaterThanOrEqualTo('validFrom', $date->format('Y-m-d H:i:s'));
        #$select->limit(2);
        #$select->order('validFrom ASC');
        #error_log('SQL: '.$select->getSqlString());
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $PriceResult = $statement->execute();

        $counter = 0;
        error_log('got: '.count($PriceResult).' prices for product id: '.$Product->id);
        $Prices = array();
        foreach($PriceResult as $row) {
            $Price = new Entity\ProductPrice();
            foreach($row as $key => $value) {
                error_log('ProductPrice set '.$key.' to '.$value);
                $Price->$key = $value;
            }
            
            /*
             * get the limitations of this price
             */
            $sql = new Sql($this->adapter);
            $select = $sql->select();
            $select->from('PriceLimit');
            $select->join('Limitation', 'PriceLimit.id = Limitation.PriceLimit_id');
            $select->where
                    ->equalTo('ProductPrice_id', $Price->id);
            $statement = $sql->prepareStatementForSqlObject($select);
            $PriceLimitResult = $statement->execute();
            foreach($PriceLimitResult as $limitrow) {
                error_log($limitrow['id'].': '.$limitrow['type'].' => '.$limitrow['value']);
            }
            
            if($counter == 0) {
                $Product->setPrice($Price);
            }
            $Prices[] = $Price;
            unset($Price);
            $counter++;
        }
        $Product->setPrices($Prices);
        error_log('LOAD: loaded '.count($Product->getPrices()).' prices');
    }
}