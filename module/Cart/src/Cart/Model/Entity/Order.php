<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cart\Model\Entity;

class Order extends Entity {
    /*
     * database fields
     */
    
    protected $id;
    protected $Purchaser_id;
    protected $PaymentType_id;
    protected $matchKey;
    protected $invoiceDetail;
    protected $Barcode_id;


    /*
     * other storage
     */
    protected $purchaser;
    protected $packages;
    
    public function getPurchaser() {
        return $this->purchaser;
    }
    public function setPurchaser(Entity\User $value) {
        $this->purchaser = $value;
    }
    
    public function getPackages() {
        return $this->packages;
    }
    public function setPackages(array $Packages) {
        foreach($Packages as $p) {
            if(get_class($p) === 'Entity\Package') {
                $this->addPackage($p);
            }
        }
    }
    public function addPackage(Entity\Package $Package) {
        $this->packages[] = $p;
    }
    /*
     * At the moment this function is just a placeholder. It's not yet clear by 
     * what we like to search a single package. Maybe the Participant_id.
     */
    public function getPackage() {
        
    }
}