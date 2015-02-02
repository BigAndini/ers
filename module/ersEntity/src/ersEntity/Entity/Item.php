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
class Item {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $Product_id;
    
    /** @ORM\Column(type="integer") */
    protected $Package_id;
    
    /** @ORM\Column(type="integer") */
    protected $Barcode_id;
    
    /** @ORM\Column(type="string", length=32) */
    protected $name;
    
    /** @ORM\Column(type="string", length=100) */
    protected $short_description;
    
    /** @ORM\Column(type="string", length=1000) */
    protected $long_description;
    
    /** @ORM\Column(type="float") */
    protected $price;

    /** @ORM\Column(type="integer") */
    protected $amount;
    
    /** @ORM\Column(type="string", length=200) */
    protected $info;
    
    /** @ORM\Column(type="string", length=32) */
    protected $status;
    
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
     * @ORM\ManyToOne(targetEntity="Package", inversedBy="items")
     * @ORM\JoinColumn(name="Package_id", referencedColumnName="id")
     **/
    private $package;
    
    /**
     * @ORM\OneToMany(targetEntity="ItemVariant", mappedBy="item")
     **/
    private $variants;
    
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
    
}