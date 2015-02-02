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
class Package {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $Order_id;
    
    /** @ORM\Column(type="integer") */
    protected $Participant_id;
    
    /** @ORM\Column(type="integer") */
    protected $Barcode_id;

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
    
    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="packages")
     * @ORM\JoinColumn(name="Order_id", referencedColumnName="id")
     **/
    private $order;
    
    /**
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="Participant_id", referencedColumnName="id")
     **/
    private $participant;
    
    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="package")
     **/
    private $items;
    
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
    
    public function getPercentage() {
        return $this->percentage;
    }
    public function setPercentage($percentage) {
        $this->percentage = $percentage;
    }
    
    public function getItems() {
        error_log('found '.count($this->items).' items');
        return $this->items;
    }
    public function getItemById($id) {
        foreach($this->items as $i) {
            if($i->getId() == $id) {
                return $i;
            }
        }
        return false;
    }
    
    public function addItem(Item $item) {
        $this->items[] = $item;
    }
}