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
class ProductVariantValue {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $ProductVariant_id;
    
    /** @ORM\Column(type="integer") */
    protected $ordering;
    
    /** @ORM\Column(type="string", length=64) */
    protected $value;

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
    
    public function getProductVariantId() {
        return $this->ProductVariant_id;
    }
    public function setProductVariantId($id) {
        $this->ProductVariant_id = $id;
    }
    
    public function getOrder() {
        return $this->ordering;
    }
    public function setOrder($order) {
        $this->ordering = $order;
    }
    
    public function getValue() {
        return $this->value;
    }
    public function setValue($value) {
        $this->value = $value;
    }
}