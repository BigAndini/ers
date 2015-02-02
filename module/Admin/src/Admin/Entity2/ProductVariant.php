<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/** 
 * @ORM\Entity 
 * @ORM\HasLifecycleCallbacks()
 */
class ProductVariant {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $Product_id;
    
    /** @ORM\Column(type="integer", nullable=true) */
    protected $ordering;

    /** @ORM\Column(type="string", length=64) */
    protected $name;
    
    /** @ORM\Column(type="string", length=16) */
    protected $type;
    
    /** @ORM\Column(type="string", length=64) */
    protected $preselection;
    
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
    
    // Other variables
    
    protected $inputFilter;
    
    public function __construct() {
        
    }
    
    public function exchangeArray($data)
    {
        foreach($data as $k => $v) {
            if(property_exists(get_class($this), $k)) {
                $this->$k = $v;
            } else {
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
    
    public function getProductId() {
        return $this->Product_id;
    }
    public function setProductId($id) {
        $this->Product_id = $id;
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
    
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        $this->type = $type;
    }
    
    public function getPreselection() {
        return $this->preselection;
    }
    public function setPreselection($preselection) {
        $this->preselection = $preselection;
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
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'type',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
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
                'name'     => 'sorting',
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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}