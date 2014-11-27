<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Catalogue\Model\Entity;

class Item extends Entity {
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

    
    public function fromProduct(\Admin\Model\Entity\Product $Product) {
        $this->Product_id = $Product->id;
        $this->name = $Product->name;
        foreach($Product->getVariants() as $variant) {
            $ItemVariant = new ItemVariant();
            $this->addVariant($ItemVariant->fromProductVariant($variant));
        }
    }
    public function addVariant($variant) {
        if(!is_object($variant)) {
            throw new Exception('Unable to add Variant which is no object', 500, null);
        }
        
        if(get_class($variant) == '\Catalogue\Model\Entity\ItemVariant') {
            $this->variants[] = $variant;
        }
    }
}