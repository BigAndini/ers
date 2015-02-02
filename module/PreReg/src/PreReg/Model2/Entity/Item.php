<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model\Entity;

use PreReg\Model;

class Item extends ServiceLocatorAwareEntity {
    protected $id;
    protected $Product_id;
    protected $Package_id;
    protected $Package_Order_id;
    protected $Barcode_id;
    protected $name;
    protected $shortDescription;
    protected $longDescription;
    protected $price;
    protected $amount;
    protected $info;
    protected $status;
    
    protected $variants;
    protected $product;
    
    private $table;

    
    public function fromProduct(\PreReg\Model\Entity\Product $Product) {
        $this->product = $Product;
        $this->Product_id = $Product->id;
        $this->name = $Product->name;
        $this->price = $Product->getPrice();
        foreach($Product->getVariants() as $variant) {
            $ItemVariant = new ItemVariant();
            $ItemVariant->fromProductVariant($variant);
            $this->addVariant($ItemVariant);
        }
    }
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $className = "PreReg\Model\\".$name."Table";
            $this->table[$name] = $this->getServiceLocator()->get($className);
            $this->table[$name]->setServiceLocator($this->sm);
        }
        return $this->table[$name];
    }
    
    public function __sleep() {
        return array(
            'id',
            'Product_id',
            'Package_id',
            'Package_Order_id',
            'Barcode_id',
            'name',
            'shortDescription',
            'longDescription',
            'price',
            'amount',
            'info',
            'status',
            'variants',
            'product',
            'updated',
            'created',
        );
    }
    
    public function exchangeArray($data) {
        parent::exchangeArray($data);
        $variant_id = array();
        $variant_value = array();
        $variant_type = array();
        foreach($data as $k => $v) {
            if(property_exists(get_class($this), $k)) {
                error_log('Entity\Item: property: '.$k.' value: '.$v);
                $this->$k = $v;
            } else {
                switch($k) {
                    case (preg_match('/variant_id_.*/', $k) ? true : false) :
                        $id = preg_replace('/^variant_id_/', '', $k);
                        error_log('got variant id '.$v.' out of '.$id);
                        $variant_id[$id] = $v;
                        break;
                    case (preg_match('/variant_value_.*/', $k) ? true : false) :
                        $id = preg_replace('/^variant_value_/', '', $k);
                        error_log('got variant value '.$v.' out of '.$id);
                        $variant_value[$id] = $v;
                        break;
                    case (preg_match('/variant_type_.*/', $k) ? true : false) :
                        $id = preg_replace('/^variant_type_/', '', $k);
                        error_log('got variant type '.$v.' out of '.$id);
                        $variant_type[$id] = $v;
                        break;
                    case 'submit':
                        # workaround for the submit button
                        break;
                    default:
                        error_log('ERROR: I do not know what to do with '.$k);
                }
            }
        }
        if(count($variant_id) == count($variant_value) && count($variant_id) == count($variant_type)) {
            for($i=0; $i<count($variant_id); $i++) {
                error_log('checking variant '.$i.' of '.count($variant_id));
                $pv = $this->getTable('ProductVariant')->getById($variant_id[$i]);
                $iv = new Model\Entity\ItemVariant();
                $iv->fromProductVariant($pv);
                switch($variant_type[$i]) {
                    case 'text':
                    case 'date':
                        $pvv = new Model\Entity\ProductVariantValue();
                        $pvv->setValue($variant_value[$i]);
                        $iv->setValue($pvv);
                        error_log('set value: '.$pvv->getValue());
                        break;
                    case 'select':
                        $pvv = $this->getTable('ProductVariantValue')->getById($variant_value[$i]);
                        $iv->setValue($pvv);
                        $iv->setProductVariantValueId($pvv->getId());
                        error_log('set value: '.$pvv->getValue());
                        break;
                }
                
            }
        } else {
            throw new \Exception('ERROR: This form was not build up correctly', 500, null);
        }
    }
    
    public function getProductId() {
        return $this->Product_id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    
    public function addVariant($variant) {
        if(!is_object($variant)) {
            throw new \Exception('Unable to add Variant which is no object', 500, null);
        }
        
        if(get_class($variant) == '\PreReg\Model\Entity\ItemVariant') {
            $this->variants[] = $variant;
        }
    }
    
    private function loadProduct($force=false) {
        if(get_class($this->product) != 'PreReg\Model\Entity\Product' || $force) {
            $this->product = $this->getTable('Product')->getById($this->Product_id);
        }
    }
    
    public function getPrice() {
        #$this->loadProduct();
        if($this->price == '') {
            $this->price = $this->product->getPrice();
        }
        return $this->price;
    }
}