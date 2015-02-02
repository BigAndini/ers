<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ersEntity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/** 
 * @ORM\Entity 
 * @ORM\HasLifecycleCallbacks()
 */
class PaymentType {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $ordering;
    
    /** @ORM\Column(type="string", length=32) */
    protected $name;
    
    /** @ORM\Column(type="string", length=256) */
    protected $logo_path;
    
    /** @ORM\Column(type="integer") */
    protected $days2payment;

    /** @ORM\Column(type="float") */
    protected $fix_fee;
    
    /** @ORM\Column(type="float") */
    protected $percentage_fee;
    
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
    
    // other variables
    
    protected $inputFilter;
    
    public function exchangeArray($data)
    {
        foreach($data as $k => $v) {
            if(property_exists(get_class($this), $k)) {
                $this->$k = $v;
            } else {
                /*if($k == 'Product_id') {
                    error_log(get_class().': set Product_id to id');
                    $this->id = $v;
                    continue;
                }*/
                error_log('ERROR: I do not know what to do with '.$k.' ('.$v.')');
            }
        }
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    // getters/setters
    
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getOrder() {
        return $this->ordering;
    }
    public function setOrder($order) {
        $this->ordering = $order;
    }
    
    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getLogoPath() {
        return $this->logo_path;
    }
    
    public function setLogoPath($path) {
        $this->logo_path = $path;
    }
    
    public function getFixFee() {
        return $this->fix_fee;
    }
    public function setFixFee($value) {
        $this->fix_fee = $value;
    }
    
    public function getPercentageFee() {
        return $this->percentage_fee;
    }
    public function setPercentageFee($percentage) {
        $this->percentage_fee = $percentage;
    }
}