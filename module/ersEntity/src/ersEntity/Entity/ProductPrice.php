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
    
    // other variables
    private $data;
    
    /**
     * @ORM\ManyToMany(targetEntity="PriceLimit")
     * @ORM\JoinTable(name="Limitation",
     *      joinColumns={@ORM\JoinColumn(name="ProductPrice_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PriceLimit_id", referencedColumnName="id")}
     *      )
     **/
    protected $limits;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="prices")
     * @ORM\JoinColumn(name="Product_id", referencedColumnName="id")
     **/
    private $product;
    
    protected $inputFilter;
    
    public function __construct() {
        $this->limits = new \Doctrine\Common\Collections\ArrayCollection();;
    }
    
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
                $this->addData($k, $v);
            }
        }
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    // getters/setters
    
    public function getData($key='') {
        if($key == '') {
            return $this->data;
        } elseif(array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else
            return false;
    }
    public function setData(array $value) {
        foreach($value as $k => $v) {
            $this->addData($k, $v);
        }
    }
    public function addData($key, $value) {
        $this->data[$key] = $value;
    }
    
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
    
    public function getLimits() {
        return $this->limits;
    }
    public function getLimitsByType($type) {
        $ret = array();
        foreach($limits as $limit) {
            if($limit->getType() == $type) {
                $ret[] = $limit;
            }
        }
        return $ret;
    }
    public function setLimits($limits) {
        foreach($limits as $limit) {
            $this->addLimit($limit);
        }
    }
    public function addLimit(PriceLimit $limit) {
        $this->limits[] = $limit;
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'Product_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Digits',
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'charge',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new \Zend\I18n\Filter\NumberFormat("en_US"),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Float',
                    ),
                ),
            )));
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}