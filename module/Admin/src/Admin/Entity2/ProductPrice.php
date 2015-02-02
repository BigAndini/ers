<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Entity 
 * @ORM\HasLifecycleCallbacks()
 */
class ProductPrice {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $Product_id;
    
    /** @ORM\Column(type="float") */
    protected $charge;

    /** @ORM\Column(type="datetime") */
    protected $updated;
    
    /** @ORM\Column(type="datetime") */
    protected $created;
    
    /**
     * @ORM\PrePersist
     */
    public function PrePersist()
    {
        if(!isset($this->created)) {
            $this->created = new \DateTime();
        }
        $this->updated = new \DateTime();
    }
    
    // getters/setters
    
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getProductId() {
        return $this->Product_id;
    }
    public function setProductId($id) {
        $this->Product_id = $id;
    }
    
    public function getCharge() {
        return $this->charge;
    }
    public function setCharge($charge) {
        $this->charge = $charge;
    }
}