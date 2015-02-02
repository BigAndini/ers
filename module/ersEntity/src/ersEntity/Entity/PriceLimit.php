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
class PriceLimit {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $ordering;
    
    /** @ORM\Column(type="string") */
    protected $type;
    
    /** @ORM\Column(type="string") */
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
    
    // other variables
    
    protected $inputFilter;
    
    public function exchangeArray($data)
    {
        foreach($data as $k => $v) {
            if(property_exists(get_class($this), $k)) {
                $setter = 'set'.ucfirst($k);
                if(method_exists($this, $setter)) {
                    $this->$setter($v);
                } else {
                    error_log(get_class().': unable to find setter: '.$setter);
                    $this->$k = $v;
                }
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
    
    public function __construct() {
        error_log(get_class().': constructor');
        $this->inputFilter = new InputFilter();
        $factory     = new InputFactory();

        /*$this->inputFilter->add($factory->createInput(array(
            'name'     => 'id',
            'required' => true,
            'filters'  => array(
                array('name' => 'Int'),
            ),
        )));*/

        /*$this->inputFilter->add($factory->createInput(array(
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
                        'max'      => 32,
                    ),
                ),
            ),
        )));*/
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
    
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        $this->type = $type;
        switch($this->type) {
            case 'deadline':
            case 'agegroup':
                $factory     = new InputFactory();
                $this->inputFilter->add($factory->createInput(array(
                    'name'     => 'value',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                        #array('name' => 'Int'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min'      => 1,
                                'max'      => 32,
                            ),
                        ),
                        /*array(
                            'name' => 'Date',
                            'options' => array(
                                #'format' => 'd.m.Y',
                                #'locale' => 'de',
                                'messages' => array(
                                    \Zend\Validator\Date::INVALID => 'The given date is not valid.',
                                    \Zend\Validator\Date::INVALID_DATE => 'The given date is not valid. (Invalid Date)',
                                    \Zend\Validator\Date::FALSEFORMAT => 'The date has not the right format.',
                                ),
                            ),
                        ),*/
                        /*array(
                            'name' => 'NotEmpty',
                            'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Date may not be empty'
                                ),
                            ),
                        ),*/
                    ),
                )));
            
                break;
            case 'counter':
                $factory     = new InputFactory();
                $this->inputFilter->add($factory->createInput(array(
                    'name'     => 'value',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                        array('name' => 'Int'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min'      => 1,
                                'max'      => 32,
                            ),
                        ),
                        array(
                            'name'    => 'Digits',
                        ),
                    ),
                )));
                break;
        }
    }
    
    public function getValue() {
        return $this->value;
    }
    public function setValue($value) {
        $this->value = $value;
    }
    
    public function getInputFilter()
    {
        if(!$this->inputFilter) {
            $this->inputFilter = new InputFilter();
            $this->setType($this->getType());
        }
        return $this->inputFilter;
    }
}