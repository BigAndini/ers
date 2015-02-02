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
class Order {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $Purchaser_id;
    
    /** @ORM\Column(type="integer") */
    protected $PaymentType_id;
    
    /** @ORM\Column(type="integer") */
    protected $Barcode_id;
    
    /** @ORM\Column(type="string") */
    protected $match_key;
    
    /** @ORM\Column(type="string") */
    protected $invoice_detail;

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
        
        $this->Purchaser_id = $this->purchaser->getId();
        $this->Barcode_id = $this->barcode->getId();
    }
    
    // other variables
    
    /**
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="Purchaser_id", referencedColumnName="id")
     **/
    private $purchaser;
    
    /**
     * @ORM\OneToOne(targetEntity="Barcode")
     * @ORM\JoinColumn(name="Barcode_id", referencedColumnName="id")
     **/
    protected $barcode;
    
    /**
     * @ORM\OneToMany(targetEntity="Package", mappedBy="order")
     **/
    private $packages;
    
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
    
    public function getPurchaser() {
        return $this->purchaser;
    }
    public function setPurchaser($user) {
        $this->purchaser = $user;
    }
    
    public function getBarcode() {
        return $this->barcode;
    }
    public function setBarcode(Barcode $barcode) {
        $this->barcode = $barcode;
    }
    
    public function getParticipants() {
        $participants = array();
        for($i = 0; $i < count($this->packages); $i++) {
            if($this->packages[$i]->getParticipant()->getPrename() != '' && $this->packages[$i]->getParticipant()->getSurname() != '') {
                $participants[$i] = $this->packages[$i]->getParticipant();
            }
        }
        
        return $participants;
    }
    
    public function addParticipant($user) {
        $package = new Package();
        $package->setParticipant($user);
        $this->packages[] = $package;
    }
    
    public function getPackages() {
        return $this->packages;
    }
    public function getPackageById($id) {
        return $this->packages[$id];
    }
    public function setItems($items) {
        foreach($items as $item) {
            $this->addItem($item);
        }
    }
    public function addItem(Item $item, $id=0) {
        $this->getPackageById($id)->addItem($item);
    }
}